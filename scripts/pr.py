import json
import os
import re
from typing import Any, Dict, Optional, Tuple
from urllib import request, error

try:
    from scripts import status
except ModuleNotFoundError:  # running as script
    import sys
    from pathlib import Path
    sys.path.append(str(Path(__file__).resolve().parent.parent))
    from scripts import status

API_ROOT = "https://api.github.com"
LABEL = "codex-automation"


def _request(method: str, url: str, data: Optional[Dict[str, Any]] = None) -> Tuple[int, Any]:
    """Perform an HTTP request to the GitHub API.

    Returns a tuple of (status_code, parsed_json_or_none).
    """
    token = os.environ.get("GITHUB_TOKEN")
    headers = {
        "Accept": "application/vnd.github+json",
        "User-Agent": "cleanwhiskers-pr-script",
    }
    if token:
        headers["Authorization"] = f"Bearer {token}"
    body = None
    if data is not None:
        body = json.dumps(data).encode("utf-8")
        headers["Content-Type"] = "application/json"
    req = request.Request(url, data=body, headers=headers, method=method)
    try:
        with request.urlopen(req) as resp:  # type: ignore[arg-type]
            status_code = resp.getcode()
            if status_code == 204:
                return status_code, None
            text = resp.read().decode("utf-8")
            return status_code, json.loads(text) if text else None
    except error.HTTPError as exc:  # pragma: no cover - network errors
        return exc.code, json.loads(exc.read().decode("utf-8") or "{}")


# ---------------------------------------------------------------------------
# Helpers to fetch task info
# ---------------------------------------------------------------------------

def _task_id_from_branch(branch: str) -> Optional[int]:
    match = re.search(r"task-(\d+)-", branch)
    if match:
        return int(match.group(1))
    return None


def _task_info(task_id: int) -> Tuple[str, int]:
    titles = status.read_tasks()
    title = titles[task_id - 1] if 0 < task_id <= len(titles) else f"Task {task_id}"
    data = status.load_status()
    attempt = 1
    for task in data.get("tasks", []):
        if task.get("id") == task_id:
            attempt = task.get("attempts", 0) + 1
            break
    return title, attempt


# ---------------------------------------------------------------------------
# GitHub helpers
# ---------------------------------------------------------------------------

def get_pr_number_by_branch(owner: str, repo: str, head: str) -> Optional[int]:
    url = f"{API_ROOT}/repos/{owner}/{repo}/pulls?head={owner}:{head}&state=open"
    status_code, data = _request("GET", url)
    if status_code == 200 and isinstance(data, list) and data:
        return data[0].get("number")
    return None


def is_pr_merged(owner: str, repo: str, pr_number: int) -> str:
    """Return merge state for a pull request.

    The function checks the PR metadata first to ensure it belongs to the
    automation system. A PR qualifies if it has the ``codex-automation`` label
    and its head branch starts with ``codex/``. If either check fails the
    function returns ``"ignored"``. Otherwise the GitHub merge API is queried
    and ``"merged"`` or ``"open"`` is returned accordingly.
    """

    info_url = f"{API_ROOT}/repos/{owner}/{repo}/pulls/{pr_number}"
    status_code, data = _request("GET", info_url)
    if status_code != 200 or not isinstance(data, dict):
        return "open"

    head_ref = data.get("head", {}).get("ref", "")
    labels = [lbl.get("name") for lbl in data.get("labels", [])]
    if LABEL not in labels or not head_ref.startswith("codex/"):
        return "ignored"

    url = f"{API_ROOT}/repos/{owner}/{repo}/pulls/{pr_number}/merge"
    status_code, _ = _request("GET", url)
    return "merged" if status_code == 204 else "open"


def _ensure_label(owner: str, repo: str, pr_number: int) -> None:
    url = f"{API_ROOT}/repos/{owner}/{repo}/issues/{pr_number}/labels"
    _request("POST", url, {"labels": [LABEL]})


# ---------------------------------------------------------------------------
# Public API
# ---------------------------------------------------------------------------

def open_or_update_pr(owner: str, repo: str, head: str, base: str = "staging") -> int:
    """Open or update a pull request for *head*.

    Ensures the PR is labelled with ``codex-automation`` and returns the PR number.
    """
    pr_number = get_pr_number_by_branch(owner, repo, head)
    if pr_number is not None:
        _ensure_label(owner, repo, pr_number)
        return pr_number

    task_id = _task_id_from_branch(head)
    title, attempt = ("", 1)
    if task_id is not None:
        title, attempt = _task_info(task_id)
    pr_title = f"[codex] Task #{task_id}: {title}" if task_id else head
    body = f"{title}\n\nAttempt #{attempt}" if title else f"Attempt #{attempt}"
    url = f"{API_ROOT}/repos/{owner}/{repo}/pulls"
    payload = {
        "title": pr_title,
        "head": head,
        "base": base,
        "body": body,
    }
    status_code, data = _request("POST", url, payload)
    if status_code not in (200, 201) or not isinstance(data, dict):
        raise RuntimeError(f"Failed to create PR: {status_code}")
    pr_number = data["number"]
    _ensure_label(owner, repo, pr_number)
    return pr_number
