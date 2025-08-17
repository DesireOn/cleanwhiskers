#!/usr/bin/env bash
set -euo pipefail

if [ $# -lt 3 ]; then
  echo "Usage: $0 TASK_ID TASK_TITLE ATTEMPT [BASE_BRANCH]" >&2
  exit 1
fi

TASK_ID="$1"
TASK_TITLE="$2"
ATTEMPT="$3"
BASE_BRANCH="${4:-staging}"

SLUG=$(python3 -c "from scripts.slug import slugify; import sys; print(slugify(sys.argv[1]))" "$TASK_TITLE")
BRANCH="codex/task-${TASK_ID}-${SLUG}"

# Ensure base branch is up to date
git fetch origin
git checkout "$BASE_BRANCH"
git pull origin "$BASE_BRANCH"

# Prepare feature branch
if git ls-remote --exit-code --heads origin "$BRANCH" >/dev/null 2>&1; then
  git fetch origin "$BRANCH":"$BRANCH"
  git checkout "$BRANCH"
else
  git checkout -b "$BRANCH" "origin/$BASE_BRANCH"
fi

# Rebase on top of latest base branch
git rebase "origin/$BASE_BRANCH"

CODEX_BIN="${CODEX_CMD:-codex}"
CODEX_STATUS=0
if [ "${CODEX_DRY_RUN:-0}" = "1" ]; then
  touch codex-dry-run.txt
else
  set +e
  "$CODEX_BIN" --task "$TASK_TITLE" --repo .
  CODEX_STATUS=$?
  set -e
fi

set +e
make test -k
TEST_STATUS=$?
set -e

git add -A
if git diff --cached --quiet; then
  if [ "${NOOP_COMMIT:-1}" = "1" ]; then
    git commit --allow-empty -m "codex: task #${TASK_ID} attempt ${ATTEMPT}"
  fi
else
  git commit -m "codex: task #${TASK_ID} attempt ${ATTEMPT}"
fi

git push --force-with-lease origin "$BRANCH"

exit $(( CODEX_STATUS || TEST_STATUS ))
