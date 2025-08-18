# Codex Automation

This guide explains how the repository's Codex automation works and how to trigger it.

## Workflow overview

The `Codex Runner` GitHub Action orchestrates automation when manually triggered or called by another workflow:

1. **Setup** – `make setup` installs PHP dependencies. The workflow also initializes task status.
2. **Fetch next task** – `scripts/task_fetcher.py` retrieves the next pending task.
3. **Run task** – `scripts/run_codex_task.sh` checks out `staging`, creates or rebases a branch named `codex/task-<id>-<slug>`, invokes the Codex CLI via `codex exec --full-auto "$TASK_TITLE" --cwd .`, and runs project tests via `make test`.
4. **Open or update PR** – `scripts/pr.py` ensures a pull request exists for the branch and applies required labels.
5. **Retry loop** – The workflow waits for the PR to merge; if not merged it retries up to three times before marking the task failed.

## Labels

Codex-managed pull requests are marked with the `codex-automation` label. Ensure this label exists in the repository. You can sync labels using `.github/labels.json` or create it manually:

```bash
# using GitHub CLI
gh label create codex-automation --color 5319e7 --description "Managed by Codex automation"
```

## Branch naming

Automation branches follow the pattern:

```
codex/task-<task-id>-<kebab-title>
```

Example: `codex/task-9-repository-documentation`.

## Environment variables

- `CODEX_CMD` – absolute path to the npm-installed Codex CLI (e.g., `$(npm bin -g)/codex`). The Python `codex-cli` is unrelated and should not be installed.
- `CODEX_DRY_RUN` – set to `1` to skip executing Codex and just run tests; useful for smoke checks.

To run Codex manually outside the workflow, use:

```bash
codex exec "Your task title or instruction" --cwd . --full-auto
```

## Running the workflow

1. Push any required changes to `staging`.
2. In GitHub, navigate to **Actions → Codex Runner**.
3. Click **Run workflow** to dispatch the automation.
4. The workflow creates or updates a PR labeled `codex-automation`. Merge it when ready.

## QA commands

`make test` runs `composer ci`, which executes PHP CS Fixer (dry-run), PHPStan, PHPUnit, and `composer audit`. These tools mirror the Symfony/PHPUnit/PHPStan toolchain used in this project.

