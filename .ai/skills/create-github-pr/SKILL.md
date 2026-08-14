---
name: create-github-pr
description: Writing as github pull request for a specified issue using the provided template
allowed-tools: Read, Bash, Grep, Glob, Edit, Write, Task, AskUserQuestion
---
# Create GitHub Pull Request for Issue

You are creating a GitHub pull request for issue #$ARGUMENTS.

## Step 1: Fetch Issue Details

First, fetch the issue details from GitHub:

```bash
gh issue view $ARGUMENTS --json title,body,labels,number
```

Read and understand:
- What the issue is asking for
- The type of change (bug fix, feature, refactor, etc.)
- Any specific requirements or acceptance criteria mentioned

Then present a summary back to me covering:

- **What you understand** the issue is asking for
- **Which files** you expect to touch and why
- **Any gaps or ambiguities** in the issue description
- **Assumptions** you'd need to make if I don't clarify

Ask me:
- Is your understanding correct?
- Is there anything not in the issue that I should know?
- Are there any related issues or existing code I should point you to?
- Are there any constraints or approaches I'd prefer?

**Do NOT proceed until I confirm your understanding is correct.**

## Step 2: Plan the Implementation

Based on the discussion, propose a plan as a numbered list covering:

1. **Explore the codebase** to understand the affected areas
2. **Identify the files** that need to be created or modified
3. **Plan your approach** considering:
    - The project's coding standards (see CLAUDE.md)
    - Existing patterns in the codebase
    - Tests to add or update
    - Migrations, seeders, or factories if needed
    - Any config or route changes
    - Service boundaries (if applicable)
    - Multi-tenancy requirements (if applicable)

Ask me:
- Does this plan look right?
- Should anything be added, removed, or done differently?
- What order makes sense — or is the proposed order fine?

**Iterate until I approve the plan.**

## Step 3: Create a Branch

### Detect whether this issue belongs to an epic

Scan the issue body for a reference to a parent epic — look for patterns like `part of #<number>`, `part of epic #<number>`, `belongs to #<number>`, or similar (case-insensitive). Extract the epic number if found.

**If the issue belongs to an epic (epic number found):**

Check whether the epic branch already exists:

```bash
git fetch origin
git branch -a | grep feature/<epic-number>
```

- If the branch **exists remotely or locally**: check it out and pull latest.
  ```bash
  git checkout feature/<epic-number> && git pull origin feature/<epic-number>
  ```
- If the branch **does not exist**: create it from main.
  ```bash
  git checkout main && git pull origin main
  git checkout -b feature/<epic-number>
  ```

All work for this child issue lands on `feature/<epic-number>`. **Do not create a per-issue branch.**

**If the issue does not belong to an epic:**

Always start from main with latest changes:

```bash
git checkout main && git pull origin main
git checkout -b <prefix>/$ARGUMENTS-<brief-description>
```

Use appropriate branch prefixes:
- `feature/` for new features
- `hotfix/` for bug fixes
- `docs/` for documentation changes

**IMPORTANT**: Never use `fix/` prefix - use `hotfix/` for bug fixes instead.

## Step 4: Implement the Changes

Make the necessary code changes following the project standards. If during implementation you encounter something unexpected — a dependency you didn't anticipate, a design decision that could go either way, or something that contradicts the plan — **stop and ask me** rather than making a silent assumption.

## Step 5: Run Quality Checks

**MANDATORY**: All quality checks must pass before proceeding to create the PR.

After implementing changes, run these quality checks in order:

### 5.1: Static Analysis

```bash
# Run PHPStan static analysis (REQUIRED - always run)
./vendor/bin/phpstan analyse --memory-limit=2G
```

### 5.2: Tests

```bash
# Run relevant tests (filter to affected functionality)
php artisan test --filter=<relevant-test-name>
```

### 5.3: Laravel Pint

```bash
# Run linting (filter to affected functionality)
vendor/bin/pint
```

### 5.4: Rector

```bash
# Run rector dry run
 "vendor/bin/rector process --dry-run"
```

### 5.5: Type Coverage

```bash
# Run rector dry run
 "vendor/bin/pest --type-coverage --min=100"
```

**CRITICAL REQUIREMENTS**:
- **PHPStan**: MUST pass with no errors. If PHPStan fails, fix all issues before proceeding. Do NOT create a baseline or ignore errors.
- **Tests**: If tests fail, fix them before proceeding.
- All quality checks must pass successfully before moving to Step 6.

## Step 6: Post-Implementation — Epic or Standalone?

Before proceeding to PR creation, determine which path to take:

### If this issue belongs to an epic:

Commit the changes to the epic branch:

```bash
git add <specific-files>
git commit -m "<commit message describing the changes for this child issue>"
git push -u origin feature/<epic-number>
```

Then use the AskUserQuestion tool to ask:

```
Question: "Changes for issue #$ARGUMENTS have been committed to feature/<epic-number>. Are all child issues in epic #<epic-number> now complete?"

Options:
1. "Yes, all tasks are done — create the PR" - Proceed to Step 6a (summary + confirmation)
2. "No, there are more tasks to do" - Stop here; report the branch and wait
```

- **If "No, more tasks to do"**: Report that the work is on `feature/<epic-number>` and stop. Do not proceed further.
- **If "Yes, all tasks done"**: Continue to Step 6a.

### If this issue does not belong to an epic:

Proceed directly to Step 6a.

---

## Step 6a: Final Confirmation Before Creating PR

Before proceeding to PR creation, determine which path to take:

### If this issue belongs to an epic:

**CRITICAL**: Do NOT commit yet. First, present a summary and get explicit approval.

#### Present a Summary

Provide the user with a clear summary:
1. List of all files modified/created/deleted
2. Brief description of changes made in each file
3. How the changes address the issue requirements
4. Any important implementation decisions or trade-offs made

Then use the AskUserQuestion tool to ask:

```
Question: "I've completed the implementation for issue #$ARGUMENTS. Please review the changes above. Are you happy for me to commit these to feature/<epic-number>?"

Options:
1. "Yes, commit the changes" (Recommended) - Commit and push to the epic branch
2. "Show me the git diff first" - Display the full diff for detailed review
3. "I need to make adjustments" - Stop and wait for user feedback
```

- **If "Show me the git diff first"**: Run `git diff` and display it, then ask for confirmation again.
- **If "I need to make adjustments"**: Stop and wait for the user to provide specific feedback.
- **If "Yes, commit the changes"**: Proceed to commit:

```bash
git add <specific-files>
git commit -m "<commit message describing the changes for this child issue>"
git push -u origin feature/<epic-number>
```

Then use the AskUserQuestion tool to ask:

```
Question: "Changes for issue #$ARGUMENTS have been committed to feature/<epic-number>. Are all child issues in epic #<epic-number> now complete?"

Options:
1. "Yes, all tasks are done — create the PR" - Proceed to Step 6a (summary + confirmation)
2. "No, there are more tasks to do" - Stop here; report the branch and wait
```

- **If "No, more tasks to do"**: Report that the work is on `feature/<epic-number>` and stop. Do not proceed further.
- **If "Yes, all tasks done"**: Continue to Step 6a.

### If this issue does not belong to an epic:

Proceed directly to Step 6a.

---

## Step 6a: Final Confirmation Before Creating PR

**CRITICAL**: This is a MANDATORY step. You MUST get explicit user approval before proceeding to create the PR.

### Present a Summary
First, provide the user with a clear summary:
1. List of all files modified/created/deleted
2. Brief description of changes made in each file
3. How the changes address the issue requirements
4. Any important implementation decisions or trade-offs made

### Request Confirmation
Then, **ALWAYS** use the AskUserQuestion tool with these exact options:

```
Question: "I've completed the implementation for issue #$ARGUMENTS. Please review the changes above. Are you ready to create the pull request?"

Options:
1. "Yes, create the PR now" (Recommended) - Proceed to create the pull request
2. "Show me the git diff first" - Display the full diff for detailed review
3. "I need to make adjustments" - Stop and wait for user feedback
```

### Handle User Response
- **If "Yes, create the PR now"**: Proceed to Step 6b (Draft Mode Question)
- **If "Show me the git diff first"**: Run `git diff` and display it, then ask for confirmation again
- **If "I need to make adjustments"**: Stop and wait for the user to provide specific feedback on what to change

**BLOCKING REQUIREMENT**: You MUST NOT proceed to Step 6b until the user explicitly selects "Yes, create the PR now".

## Step 6b: Determine PR Draft Status

**After receiving approval to create the PR**, use the AskUserQuestion tool to ask about draft mode:

```
Question: "Should this pull request be created as a draft or ready for review?"

Options:
1. "Create as draft" (Recommended) - Create the PR in draft mode, which can be marked as ready for review later
2. "Ready for review" - Create the PR ready for immediate review
```

### Store the Response
- Store the user's choice to use in Step 7
- **If "Create as draft"**: Add the `--draft` flag when creating the PR
- **If "Ready for review"**: Create the PR without the draft flag

**BLOCKING REQUIREMENT**: You MUST NOT proceed to Step 7 or Step 8 until the user has answered this question.

## Step 7: Commit and Create PR

**ONLY PROCEED WITH THIS STEP AFTER RECEIVING EXPLICIT USER APPROVAL IN STEP 6a AND STEP 6b**

Once the user has explicitly approved by selecting "Yes, create the PR now" and chosen the draft status, commit the changes (if not already committed) and create the PR.

### Commit the changes (standalone issues only — epic issues were already committed in Step 6):
```bash
git add <specific-files>
git commit -m "<commit message describing the changes>"
git push -u origin <branch-name>
```

**IMPORTANT**:
- Never add "Co-Authored-By: Claude" or similar attribution to commit messages.
- Never add "Generated with Claude Code" or similar attribution to PR descriptions.

### Create the PR with automatic reviewers:

**IMPORTANT**:
- Include the `--draft` flag if the user selected "Create as draft" in Step 6b.

Use the appropriate command based on draft status and whether this is an epic or standalone issue.

For **epic issues**, the `Fixes` line should reference the epic number, and include a `Part of` line listing all child issues. For **standalone issues**, only reference the single issue.

**If user chose "Create as draft":**
```bash
gh pr create --title "<PR title>" --draft --body "$(cat <<'EOF'
# Pull Request

## Description
<brief description of changes>

## Related Issues
<!-- Epic example: -->
Fixes #<epic-number>
Part of: #<child-issue-1>, #<child-issue-2>, ...

<!-- Standalone example: -->
Fixes #$ARGUMENTS

## Additional Context
<!-- Add any other context about the PR here -->

## Deployment Notes
<!-- Any special deployment steps or environment changes needed? -->
EOF
)"
```

**If user chose "Ready for review":**
```bash
gh pr create --title "<PR title>" --body "$(cat <<'EOF'
# Pull Request

## Description
<brief description of changes>

## Related Issues
<!-- Epic example: -->
Fixes #<epic-number>
Part of: #<child-issue-1>, #<child-issue-2>, ...

<!-- Standalone example: -->
Fixes #$ARGUMENTS

## Additional Context
<!-- Add any other context about the PR here -->

## Deployment Notes
<!-- Any special deployment steps or environment changes needed? -->
EOF
)"
```

## Step 8: Report Back

After creating the PR, provide the user with:
- The PR URL
- A summary of what was implemented
- Any notes about the implementation or things to be aware of
