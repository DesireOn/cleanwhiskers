import json
import threading

from scripts import status


def test_init_creates_file(tmp_path, monkeypatch):
    tasks = tmp_path / "tasks.yaml"
    tasks.write_text("- A task\n")
    monkeypatch.chdir(tmp_path)
    status.main(["--init"])
    data = json.loads((tmp_path / ".task_status.json").read_text())
    assert data["meta"]["schemaVersion"] == status.SCHEMA_VERSION
    assert data["tasks"][0]["title"] == "A task"


def test_init_idempotent(tmp_path, monkeypatch):
    tasks = tmp_path / "tasks.yaml"
    tasks.write_text("- First task\n")
    monkeypatch.chdir(tmp_path)
    status.main(["--init"])
    first = (tmp_path / ".task_status.json").read_text()
    status.main(["--init"])
    second = (tmp_path / ".task_status.json").read_text()
    assert first == second


def test_concurrent_writes(tmp_path, monkeypatch):
    monkeypatch.chdir(tmp_path)
    data1 = {"tasks": [{"id": 1, "title": "A", "attempts": 0, "status": "pending", "last_pr": 0}], "meta": {"schemaVersion": 1}}
    data2 = {"tasks": [{"id": 1, "title": "B", "attempts": 0, "status": "pending", "last_pr": 0}], "meta": {"schemaVersion": 1}}

    def writer(data):
        status.save_status(data, path=tmp_path / ".task_status.json")

    t1 = threading.Thread(target=writer, args=(data1,))
    t2 = threading.Thread(target=writer, args=(data2,))
    t1.start()
    t2.start()
    t1.join()
    t2.join()

    with open(tmp_path / ".task_status.json") as fh:
        json.load(fh)  # should not be corrupted
