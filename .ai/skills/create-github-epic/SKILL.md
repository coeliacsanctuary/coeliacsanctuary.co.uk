---
name: create-github-epic
description: Writing as github epic
allowed-tools: Read, Bash, Grep, Glob, Edit, Write, Task, AskUserQuestion
---

# Create GitHub Epic

Create a well-structured GitHub epic with subtasks using the `gh` CLI. This is an interactive, conversational workflow — ask questions at every stage.

## Step 1: Verify Environment

Run these checks silently before anything else:

1. `gh auth status` — confirm authentication
2. `gh repo view --json nameWithOwner -q '.nameWithOwner'` — confirm repo context
3. `gh label list --limit 100` — discover existing labels

If any check fails, stop and help me resolve it.

## Step 2: Understand the Epic

Ask me conversational questions to build a clear picture. Do NOT rush to create issues. Cover:

**Core understanding:**
- What is the high-level goal or feature?
- What problem does this solve or what value does it deliver?
- Who is affected (end users, developers, ops)?
_
_**Scope and boundaries:**
- What is explicitly in scope?
- What is explicitly out of scope?
- Are there any dependencies or blockers?

**Acceptance criteria:**
- How will you know this epic is complete?
- Are there specific measurable outcomes?

Summarise your understanding back to me and confirm before proceeding.

## Step 3: Plan Subtasks

Break the epic into subtasks collaboratively:

1. Propose an initial breakdown as a numbered list with a one-line description each
2. Ask me to review — are any missing, should any be split or merged, is the priority right?
3. For each subtask, clarify: description, acceptance criteria, estimated size, assignee
4. Iterate until I confirm the subtask list

## Step 4: Labels and Metadata

Reference existing repo labels from Step 1. Ask about:

- **Type labels:** `epic`, `feature`, `bug`, `chore`, `refactor`, `spike`, `documentation`
- **Priority labels:** `priority: high`, `priority: medium`, `priority: low`
- **Size labels:** `size: s`, `size: m`, `size: l`
- **Area labels:** e.g. `area: api`, `area: frontend`
- **Custom labels:** Offer to create new ones if needed
- **Milestone and assignees**

If the repo has no `epic` label, offer to create one (colour `7B61FF`, description "Tracks a body of work spanning multiple issues").

Present a summary table of all issues with proposed labels before proceeding.

## Step 5: Create the Epic

Compose the epic body using this template:

```
## Overview
<one_paragraph_summary>

## Goals
- <goal_1>
- <goal_2>

## Acceptance Criteria
- [ ] <criterion_1>
- [ ] <criterion_2>

## Subtasks
- [ ] <placeholder — will be updated with issue numbers>

## Notes
<additional_context>
```

Create with: `gh issue create --title "Epic: <title>" --body "<body>" --label "<labels>" --milestone "<milestone>" --assignee "<user>"`

Capture the returned issue number.

## Step 6: Create Subtask Issues

For each subtask:

1. Compose body including description, acceptance criteria, and `Part of #<epic_number>`
2. Create with: `gh issue create --title "<title>" --body "<body>" --label "<labels>" --assignee "<user>"`
3. Capture each issue number

## Step 7: Link Everything

1. Update the epic body to replace placeholders with actual issue numbers:
   ```
- [ ] #123 - Subtask title
- [ ] #124 - Another subtask
   ```
2. Use `gh issue edit <epic_number> --body "<updated_body>"`
3. Present a final summary showing the epic and all subtasks with issue numbers and links

## Principles

- **Always ask, never assume.** If unsure about scope, labels, priority, or assignees — ask.
- **Confirm before creating.** Present a full summary and get explicit "go" before running any `gh issue create` commands.
- **Respect existing conventions.** Match the repo's existing labels and naming patterns.
- **Iterate subtasks.** The breakdown is the most valuable part — spend time getting it right.
- **Keep bodies concise.** Clear and scannable, not walls of text.
