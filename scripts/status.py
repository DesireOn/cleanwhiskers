import argparse
import json
import os
import tempfile
from pathlib import Path
from typing import Callable, List

import fcntl


class VersionConflict(RuntimeError):
    """Raised when the status file was modified by another process."""


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
        return {"tasks": [], "meta": {"schemaVersion": SCHEMA_VERSION, "version": 0}}
    with open(path, "r") as fh:
        _lock_file(fh.fileno())
        try:
            data = json.load(fh)
        finally:
            _unlock_file(fh.fileno())
    data.setdefault("meta", {})
    data["meta"].setdefault("schemaVersion", SCHEMA_VERSION)
    data["meta"].setdefault("version", 0)
    return data


def save_status(data: dict, path: Path = STATUS_FILE, *, expected_version: int | None = None) -> None:
    """Atomically save *data* to *path* using a temp file and rename.

    If ``expected_version`` is provided the current file version is compared
    before writing and :class:`VersionConflict` is raised when it differs.
    """
    path.parent.mkdir(parents=True, exist_ok=True)
    fd = os.open(LOCK_FILE, os.O_CREAT | os.O_RDWR)
    try:
        _lock_file(fd)
        if expected_version is not None:
            current = _load_status_unlocked(path)
            current_version = current.get("meta", {}).get("version", 0)
            if current_version != expected_version:
                raise VersionConflict("status file changed")
        tmp_fd, tmp_path = tempfile.mkstemp(dir=str(path.parent))
        with os.fdopen(tmp_fd, "w") as tmp_fh:
            json.dump(data, tmp_fh, indent=2)
            tmp_fh.flush()
            os.fsync(tmp_fh.fileno())
        os.replace(tmp_path, path)
    finally:
        _unlock_file(fd)
        os.close(fd)


def _load_status_unlocked(path: Path = STATUS_FILE) -> dict:
    if not path.exists():
        return {"tasks": [], "meta": {"schemaVersion": SCHEMA_VERSION, "version": 0}}
    with open(path, "r") as fh:
        data = json.load(fh)
    data.setdefault("meta", {})
    data["meta"].setdefault("schemaVersion", SCHEMA_VERSION)
    data["meta"].setdefault("version", 0)
    return data


def _save_status_unlocked(data: dict, path: Path = STATUS_FILE) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    tmp_fd, tmp_path = tempfile.mkstemp(dir=str(path.parent))
    with os.fdopen(tmp_fd, "w") as tmp_fh:
        json.dump(data, tmp_fh, indent=2)
        tmp_fh.flush()
        os.fsync(tmp_fh.fileno())
    os.replace(tmp_path, path)


def _update_task(task_id: int, updater: Callable[[dict], None]) -> None:
    while True:
        data = load_status()
        version = data.get("meta", {}).get("version", 0)
        target = None
        for task in data.get("tasks", []):
            if task.get("id") == task_id:
                target = task
                break
        if target is None:
            return
        before = json.dumps(target, sort_keys=True)
        updater(target)
        if before == json.dumps(target, sort_keys=True):
            return
        data["meta"]["version"] = version + 1
        try:
            save_status(data, expected_version=version)
            return
        except VersionConflict:
            continue


# ---------------------------------------------------------------------------
# Public API
# ---------------------------------------------------------------------------


def begin_attempt(task_id: int) -> None:
    def updater(task: dict) -> None:
        if task.get("status") != "in_progress":
            task["attempts"] = task.get("attempts", 0) + 1
            task["status"] = "in_progress"

    _update_task(task_id, updater)


def mark_merged(task_id: int, pr_number: int) -> None:
    def updater(task: dict) -> None:
        task["status"] = "merged"
        task["last_pr"] = pr_number

    _update_task(task_id, updater)


def mark_failed(task_id: int, pr_number: int) -> None:
    def updater(task: dict) -> None:
        task["last_pr"] = pr_number
        if task.get("attempts", 0) >= 3:
            task["status"] = "failed"
        else:
            task["status"] = "pending"

    _update_task(task_id, updater)


def update_last_pr(task_id: int, pr_number: int) -> None:
    def updater(task: dict) -> None:
        task["last_pr"] = pr_number

    _update_task(task_id, updater)


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
        "meta": {"schemaVersion": SCHEMA_VERSION, "version": 0},
    }
    save_status(data)


def main(argv: List[str] | None = None) -> None:
    parser = argparse.ArgumentParser(description="Task status helper")
    parser.add_argument("--init", action="store_true", help="create status file")
    parser.add_argument("--begin-attempt", type=int, metavar="TASK_ID", help="increment attempts and mark in progress")
    parser.add_argument("--mark-merged", nargs=2, type=int, metavar=("TASK_ID", "PR"), help="mark task as merged")
    parser.add_argument("--mark-failed", nargs=2, type=int, metavar=("TASK_ID", "PR"), help="mark task as failed or pending")
    parser.add_argument("--update-last-pr", nargs=2, type=int, metavar=("TASK_ID", "PR"), help="update last PR number")
    args = parser.parse_args(argv)
    if args.init:
        init_status()
    if args.begin_attempt is not None:
        begin_attempt(args.begin_attempt)
    if args.mark_merged:
        tid, pr = args.mark_merged
        mark_merged(tid, pr)
    if args.mark_failed:
        tid, pr = args.mark_failed
        mark_failed(tid, pr)
    if args.update_last_pr:
        tid, pr = args.update_last_pr
        update_last_pr(tid, pr)


if __name__ == "__main__":
    main()
