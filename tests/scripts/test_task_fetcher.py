import json
import sys
from pathlib import Path

import pytest

# Ensure repository root on path for importing scripts package
sys.path.insert(0, str(Path(__file__).resolve().parents[2]))

from scripts import task_fetcher, status


def _write_status(tmp_path: Path, tasks: list[dict]) -> None:
    data = {"tasks": tasks, "meta": {"schemaVersion": status.SCHEMA_VERSION}}
    (tmp_path / ".task_status.json").write_text(json.dumps(data, indent=2))


def test_fetch_marks_in_progress(tmp_path, monkeypatch):
    (tmp_path / "tasks.yaml").write_text("- A\n- B\n")
    monkeypatch.chdir(tmp_path)
    task = task_fetcher.fetch_next_task()
    assert task == {"id": 1, "title": "A"}
    data = json.loads((tmp_path / ".task_status.json").read_text())
    assert data["tasks"][0]["status"] == "in_progress"


def test_second_run_picks_next(tmp_path, monkeypatch):
    (tmp_path / "tasks.yaml").write_text("- A\n- B\n")
    monkeypatch.chdir(tmp_path)
    first = task_fetcher.fetch_next_task()
    assert first["id"] == 1
    second = task_fetcher.fetch_next_task()
    assert second["id"] == 2


def test_skips_done_and_failed(tmp_path, monkeypatch, capsys):
    (tmp_path / "tasks.yaml").write_text("- A\n- B\n")
    _write_status(tmp_path, [
        {"id": 1, "title": "A", "attempts": 1, "status": "done", "last_pr": 0},
        {"id": 2, "title": "B", "attempts": 3, "status": "failed", "last_pr": 0},
    ])
    monkeypatch.chdir(tmp_path)
    with pytest.raises(SystemExit) as exc:
        task_fetcher.main(["--print-json"])
    assert exc.value.code == 20
    assert capsys.readouterr().out.strip() == "{}"


def test_attempt_limit(tmp_path, monkeypatch):
    (tmp_path / "tasks.yaml").write_text("- A\n- B\n")
    _write_status(tmp_path, [
        {"id": 1, "title": "A", "attempts": 3, "status": "pending", "last_pr": 0},
        {"id": 2, "title": "B", "attempts": 0, "status": "pending", "last_pr": 0},
    ])
    monkeypatch.chdir(tmp_path)
    task = task_fetcher.fetch_next_task()
    assert task == {"id": 2, "title": "B"}


def test_empty_tasks_exit_code(tmp_path, monkeypatch, capsys):
    (tmp_path / "tasks.yaml").write_text("")
    monkeypatch.chdir(tmp_path)
    with pytest.raises(SystemExit) as exc:
        task_fetcher.main(["--print-json"])
    assert exc.value.code == 20
    assert capsys.readouterr().out.strip() == "{}"
