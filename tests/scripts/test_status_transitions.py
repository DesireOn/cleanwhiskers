import json
import sys
from pathlib import Path

import pytest

# Ensure repository root on path for importing scripts package
sys.path.insert(0, str(Path(__file__).resolve().parents[2]))

from scripts import status  # noqa: E402


def _write_status(tmp_path: Path, tasks: list[dict]) -> None:
    data = {"tasks": tasks, "meta": {"schemaVersion": status.SCHEMA_VERSION}}
    (tmp_path / ".task_status.json").write_text(json.dumps(data, indent=2))


def _read_status(tmp_path: Path) -> dict:
    return json.loads((tmp_path / ".task_status.json").read_text())


def test_begin_attempt_idempotent(tmp_path, monkeypatch):
    _write_status(tmp_path, [{"id": 1, "title": "A", "attempts": 0, "status": "pending", "last_pr": 0}])
    monkeypatch.chdir(tmp_path)
    status.begin_attempt(1)
    status.begin_attempt(1)
    data = _read_status(tmp_path)
    assert data["tasks"][0]["attempts"] == 1
    assert data["tasks"][0]["status"] == "in_progress"


def test_attempt_flow_and_failure(tmp_path, monkeypatch):
    _write_status(tmp_path, [{"id": 1, "title": "A", "attempts": 0, "status": "pending", "last_pr": 0}])
    monkeypatch.chdir(tmp_path)

    status.begin_attempt(1)
    status.mark_failed(1, 10)
    data = _read_status(tmp_path)
    assert data["tasks"][0]["attempts"] == 1
    assert data["tasks"][0]["status"] == "pending"
    assert data["tasks"][0]["last_pr"] == 10

    status.begin_attempt(1)
    status.mark_failed(1, 11)
    data = _read_status(tmp_path)
    assert data["tasks"][0]["attempts"] == 2
    assert data["tasks"][0]["status"] == "pending"
    assert data["tasks"][0]["last_pr"] == 11

    status.begin_attempt(1)
    status.mark_failed(1, 12)
    data = _read_status(tmp_path)
    assert data["tasks"][0]["attempts"] == 3
    assert data["tasks"][0]["status"] == "failed"
    assert data["tasks"][0]["last_pr"] == 12


def test_mark_merged_and_update_last_pr(tmp_path, monkeypatch):
    _write_status(tmp_path, [{"id": 1, "title": "A", "attempts": 1, "status": "in_progress", "last_pr": 0}])
    monkeypatch.chdir(tmp_path)
    status.mark_merged(1, 50)
    status.mark_merged(1, 50)
    data = _read_status(tmp_path)
    assert data["tasks"][0]["status"] == "merged"
    assert data["tasks"][0]["last_pr"] == 50
    assert data["tasks"][0]["attempts"] == 1

    status.update_last_pr(1, 51)
    data = _read_status(tmp_path)
    assert data["tasks"][0]["last_pr"] == 51
