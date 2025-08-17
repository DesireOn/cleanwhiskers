import argparse
import json
import os
import tempfile
from typing import Any, Dict, Optional

import fcntl

try:
    from scripts import status
except ModuleNotFoundError:  # running as script
    import sys
    from pathlib import Path
    sys.path.append(str(Path(__file__).resolve().parent.parent))
    from scripts import status

# Constants reused from status module
TASKS_FILE = status.TASKS_FILE
STATUS_FILE = status.STATUS_FILE
LOCK_FILE = status.LOCK_FILE


# ---------------------------------------------------------------------------
# Core logic
# ---------------------------------------------------------------------------

def _read_tasks() -> list[str]:
    return status.read_tasks(TASKS_FILE)


def _load_status_unlocked() -> Dict[str, Any]:
    if not STATUS_FILE.exists():
        return {"tasks": [], "meta": {"schemaVersion": status.SCHEMA_VERSION}}
    with open(STATUS_FILE, "r") as fh:
        return json.load(fh)


def _save_status_unlocked(data: Dict[str, Any]) -> None:
    STATUS_FILE.parent.mkdir(parents=True, exist_ok=True)
    tmp_fd, tmp_path = tempfile.mkstemp(dir=str(STATUS_FILE.parent))
    with os.fdopen(tmp_fd, "w") as tmp_fh:
        json.dump(data, tmp_fh, indent=2)
        tmp_fh.flush()
        os.fsync(tmp_fh.fileno())
    os.replace(tmp_path, STATUS_FILE)


def fetch_next_task() -> Optional[Dict[str, Any]]:
    tasks = _read_tasks()
    fd = os.open(LOCK_FILE, os.O_CREAT | os.O_RDWR)
    try:
        fcntl.flock(fd, fcntl.LOCK_EX)
        data = _load_status_unlocked()
        existing = {t["title"]: t for t in data.get("tasks", [])}
        new_tasks = []
        chosen: Optional[Dict[str, Any]] = None
        for idx, title in enumerate(tasks, start=1):
            entry = existing.get(title, {"attempts": 0, "status": "pending", "last_pr": 0})
            entry = {**entry, "id": idx, "title": title}
            if chosen is None and entry["status"] == "pending" and entry["attempts"] < 3:
                entry["status"] = "in_progress"
                chosen = {"id": entry["id"], "title": entry["title"]}
            new_tasks.append(entry)
        data["tasks"] = new_tasks
        _save_status_unlocked(data)
    finally:
        fcntl.flock(fd, fcntl.LOCK_UN)
        os.close(fd)
    return chosen


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def main(argv: list[str] | None = None) -> None:
    parser = argparse.ArgumentParser(description="Fetch next task to work on")
    parser.add_argument("--print-json", action="store_true", help="Print next task as JSON")
    args = parser.parse_args(argv)
    task = fetch_next_task()
    if args.print_json:
        print(json.dumps(task or {}))
    if task is None:
        raise SystemExit(20)


if __name__ == "__main__":
    main()
