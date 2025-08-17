import argparse
import json
import os
import tempfile
from pathlib import Path
from typing import List

import fcntl

SCHEMA_VERSION = 1
TASKS_FILE = Path("tasks.yaml")
STATUS_FILE = Path(".task_status.json")
LOCK_FILE = STATUS_FILE.with_suffix(".lock")


# ---------------------------------------------------------------------------
# File helpers
# ---------------------------------------------------------------------------

def _lock_file(fd: int) -> None:
    fcntl.flock(fd, fcntl.LOCK_EX)


def _unlock_file(fd: int) -> None:
    fcntl.flock(fd, fcntl.LOCK_UN)


def load_status(path: Path = STATUS_FILE) -> dict:
    """Load status JSON. Returns empty skeleton if file missing."""
    if not path.exists():
        return {"tasks": [], "meta": {"schemaVersion": SCHEMA_VERSION}}
    with open(path, "r") as fh:
        _lock_file(fh.fileno())
        try:
            data = json.load(fh)
        finally:
            _unlock_file(fh.fileno())
    return data


def save_status(data: dict, path: Path = STATUS_FILE) -> None:
    """Atomically save *data* to *path* using a temp file and rename."""
    path.parent.mkdir(parents=True, exist_ok=True)
    fd = os.open(LOCK_FILE, os.O_CREAT | os.O_RDWR)
    try:
        _lock_file(fd)
        tmp_fd, tmp_path = tempfile.mkstemp(dir=str(path.parent))
        with os.fdopen(tmp_fd, "w") as tmp_fh:
            json.dump(data, tmp_fh, indent=2)
            tmp_fh.flush()
            os.fsync(tmp_fh.fileno())
        os.replace(tmp_path, path)
    finally:
        _unlock_file(fd)
        os.close(fd)


# ---------------------------------------------------------------------------
# Task parsing
# ---------------------------------------------------------------------------

def read_tasks(path: Path = TASKS_FILE) -> List[str]:
    tasks: List[str] = []
    if not path.exists():
        return tasks
    with open(path, "r") as fh:
        for line in fh:
            line = line.strip()
            if not line or line.startswith("#"):
                continue
            if line.startswith("-"):
                line = line[1:].strip()
            tasks.append(line.strip('"'))
    return tasks


# ---------------------------------------------------------------------------
# CLI operations
# ---------------------------------------------------------------------------

def init_status() -> None:
    if STATUS_FILE.exists():
        # already initialised
        return
    titles = read_tasks()
    data = {
        "tasks": [
            {
                "id": idx,
                "title": title,
                "attempts": 0,
                "status": "pending",
                "last_pr": 0,
            }
            for idx, title in enumerate(titles, start=1)
        ],
        "meta": {"schemaVersion": SCHEMA_VERSION},
    }
    save_status(data)


def main(argv: List[str] | None = None) -> None:
    parser = argparse.ArgumentParser(description="Task status helper")
    parser.add_argument("--init", action="store_true", help="create status file")
    args = parser.parse_args(argv)
    if args.init:
        init_status()


if __name__ == "__main__":
    main()
