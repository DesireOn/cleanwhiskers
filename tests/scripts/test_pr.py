import json
import sys
from pathlib import Path

import pytest

# Ensure repository root on path for importing scripts package
sys.path.insert(0, str(Path(__file__).resolve().parents[2]))

from scripts import pr  # noqa: E402


def _write_status(tmp_path: Path, tasks: list[dict]) -> None:
    data = {"tasks": tasks, "meta": {"schemaVersion": 1}}
    (tmp_path / ".task_status.json").write_text(json.dumps(data))


def test_open_or_update_pr_creates_and_labels(tmp_path, monkeypatch):
    (tmp_path / "tasks.yaml").write_text("- Do something\n")
    _write_status(tmp_path, [{"id": 1, "title": "Do something", "attempts": 0, "status": "pending", "last_pr": 0}])
    monkeypatch.chdir(tmp_path)
    monkeypatch.setenv("GITHUB_TOKEN", "token")

    calls = []

    def fake_request(method, url, data=None):
        calls.append((method, url, data))
        if url.endswith("/pulls?head=owner:codex/task-1-do-something&state=open"):
            return 200, []
        if url.endswith("/pulls") and method == "POST":
            assert data["title"] == "[codex] Task #1: Do something"
            assert data["body"] == "Do something\n\nAttempt #1"
            return 201, {"number": 5}
        if url.endswith("/issues/5/labels"):
            assert data == {"labels": [pr.LABEL]}
            return 200, {}
        raise AssertionError(f"Unexpected call {method} {url}")

    monkeypatch.setattr(pr, "_request", fake_request)

    number = pr.open_or_update_pr("owner", "repo", "codex/task-1-do-something")
    assert number == 5
    assert [c[0] for c in calls] == ["GET", "POST", "POST"]


def test_open_or_update_pr_existing(monkeypatch, tmp_path):
    (tmp_path / "tasks.yaml").write_text("- Do something\n")
    _write_status(tmp_path, [{"id": 1, "title": "Do something", "attempts": 1, "status": "pending", "last_pr": 5}])
    monkeypatch.chdir(tmp_path)
    monkeypatch.setenv("GITHUB_TOKEN", "token")

    calls = []

    def fake_request(method, url, data=None):
        calls.append((method, url, data))
        if url.endswith("/pulls?head=owner:codex/task-1-do-something&state=open"):
            return 200, [{"number": 5}]
        if url.endswith("/issues/5/labels"):
            return 200, {}
        raise AssertionError(f"Unexpected call {method} {url}")

    monkeypatch.setattr(pr, "_request", fake_request)

    number = pr.open_or_update_pr("owner", "repo", "codex/task-1-do-something")
    assert number == 5
    assert [c[0] for c in calls] == ["GET", "POST"]


def test_is_pr_merged_states(monkeypatch):
    calls = []

    def fake_request(method, url, data=None):
        calls.append(url)
        if url.endswith("/pulls/1"):
            return 200, {"head": {"ref": "codex/branch"}, "labels": [{"name": pr.LABEL}]}
        if url.endswith("/pulls/1/merge"):
            return 204, None
        raise AssertionError("unexpected url")

    monkeypatch.setattr(pr, "_request", fake_request)
    assert pr.is_pr_merged("owner", "repo", 1) == "merged"
    assert calls == [
        f"{pr.API_ROOT}/repos/owner/repo/pulls/1",
        f"{pr.API_ROOT}/repos/owner/repo/pulls/1/merge",
    ]

    def fake_request_open(method, url, data=None):
        if url.endswith("/pulls/1"):
            return 200, {"head": {"ref": "codex/branch"}, "labels": [{"name": pr.LABEL}]}
        if url.endswith("/pulls/1/merge"):
            return 404, None
        raise AssertionError("unexpected url")

    monkeypatch.setattr(pr, "_request", fake_request_open)
    assert pr.is_pr_merged("owner", "repo", 1) == "open"

    def fake_request_ignored_label(method, url, data=None):
        return 200, {"head": {"ref": "codex/branch"}, "labels": []}

    monkeypatch.setattr(pr, "_request", fake_request_ignored_label)
    assert pr.is_pr_merged("owner", "repo", 1) == "ignored"

    def fake_request_ignored_prefix(method, url, data=None):
        return 200, {"head": {"ref": "feature"}, "labels": [{"name": pr.LABEL}]}

    monkeypatch.setattr(pr, "_request", fake_request_ignored_prefix)
    assert pr.is_pr_merged("owner", "repo", 1) == "ignored"


def test_get_pr_number_by_branch(monkeypatch):
    def fake_request(method, url, data=None):
        return 200, [{"number": 99}]

    monkeypatch.setattr(pr, "_request", fake_request)
    num = pr.get_pr_number_by_branch("owner", "repo", "feature")
    assert num == 99

    def fake_request_none(method, url, data=None):
        return 200, []

    monkeypatch.setattr(pr, "_request", fake_request_none)
    assert pr.get_pr_number_by_branch("owner", "repo", "feature") is None
