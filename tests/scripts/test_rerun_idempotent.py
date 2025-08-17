import sys
from pathlib import Path

import pytest

# Ensure repository root on path for importing scripts package
sys.path.insert(0, str(Path(__file__).resolve().parents[2]))

from scripts import pr, status  # noqa: E402


def test_rerun_does_not_duplicate_pr_or_attempts(tmp_path, monkeypatch):
    monkeypatch.chdir(tmp_path)
    (tmp_path / "tasks.yaml").write_text("- Do something\n")
    status.init_status()
    monkeypatch.setenv("GITHUB_TOKEN", "token")

    created = {"flag": False}
    create_calls = {"count": 0}

    def fake_request(method, url, data=None):
        if url.endswith("/pulls?head=owner:codex/task-1-do-something&state=open"):
            if created["flag"]:
                return 200, [{"number": 5}]
            return 200, []
        if url.endswith("/pulls") and method == "POST":
            create_calls["count"] += 1
            created["flag"] = True
            return 201, {"number": 5}
        if url.endswith("/issues/5/labels"):
            return 200, {}
        raise AssertionError(f"Unexpected call {method} {url}")

    monkeypatch.setattr(pr, "_request", fake_request)

    # First run
    pr_number = pr.open_or_update_pr("owner", "repo", "codex/task-1-do-something")
    assert pr_number == 5
    status.begin_attempt(1)
    data = status.load_status()
    assert data["tasks"][0]["attempts"] == 1
    version = data["meta"]["version"]

    # Second run (after crash)
    pr_number_2 = pr.open_or_update_pr("owner", "repo", "codex/task-1-do-something")
    assert pr_number_2 == 5
    status.begin_attempt(1)
    data2 = status.load_status()
    assert data2["tasks"][0]["attempts"] == 1
    assert data2["meta"]["version"] == version
    assert create_calls["count"] == 1
