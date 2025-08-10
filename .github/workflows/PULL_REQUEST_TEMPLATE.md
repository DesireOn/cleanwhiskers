What & Why
Describe the change and the user/technical problem it solves. Link to the issue (e.g., #123).

Issue: #

Context/Decision notes: (architecture, trade-offs, alternatives considered)

Changes
Brief bullet list of key changes (files, endpoints, entities).

Added …

Modified …

Removed …

Acceptance Criteria
Check each item you satisfied.

 Behavior matches the user story

 Edge cases handled (invalid input, missing data, errors)

 HTTP responses and status codes are correct

 No regressions in existing features

Test Plan
How did you verify it works? Include exact commands/logs if helpful.

 Unit tests added/updated (tests/Unit/...)

 Functional/integration tests added/updated (tests/Functional/...)

 composer test is green locally

 Manual check (steps):

Database
Complete if schema or data changed.

 New/changed entities

 Migration included (migrations/)

 Backfilled/fixture updates as needed

 Rollback strategy (how to revert)

Security / Perf / Reliability
 Inputs validated; exceptions mapped to proper HTTP codes

 No secrets/PII in code or logs

 Potential N+1 queries avoided; indexes considered

 Idempotency / retries considered where relevant

DX / Documentation
 Code follows PSR-12; composer lint:php passes

 Static analysis clean; composer stan passes

 Updated README/AGENTS.md if developer steps changed

 Feature flags or config toggles documented (if any)

Screenshots / Evidence (optional)
Attach screenshots, curl examples, or before/after responses if applicable.

Checklist (CI must pass before review)
 composer ci passes (style, stan, tests, audit)

 No TODOs left in changed code

 Reviewed risk & rollback notes below

Risks / Rollback
List any risks and how to mitigate. Provide the exact rollback steps/commands.