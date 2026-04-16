---
name: create-github-issue
description: Writing and maintaining GitHub issues for the journey-tracker.cloud repository. Use when creating new issues, editing issue titles/bodies or cleaning up issue metadata (types, labels).
agent: Explore
allowed-tools: Read, Bash, Grep, Glob, AskUserQuestion
---
# Writing and maintaining GitHub issues

## Title Standards
- **Sentence case** - Capitalize only the first word and proper nouns.
- **No type prefixes** - Use GitHub issue types, not Bug:, Feature: etc...
- **Imperative mood for enhancements** - "Fix N+1 issue" not "Fixing N+1 issue"
- **Descriptive for bugs** - Describe the symptom "Failed to allocate booking to staff member"
- **Specific** - "Be specific, it must be understandable without opening the issue body"

## Issue types
Set via the GitHub GraphQL API after creating the issue (the --type flag is not reliably supported):

- **Bug** - Something isn't working as expected
- **Feature** - New capability or improvement
- **Task** - Internal task or chore
- **Epic** - Large feature or project

## Bug

When writing a bug issue, after the user has explained what the problem is, explore the codebase for possible solutions. Use Chrome if needed to explore the problem. Do not implement the fix, instead write clear and concise explanation under "Proposed Solution" in the issue body.

## Big Task Detection

Before creating an issue, evaluate whether the request is a "big task" that should be an Epic with subtasks. A task qualifies as big if it has:

- **Multiple distinct components** - The work involves several separate features or pieces
- **Cross-cutting concerns** - Requires work across different areas (frontend, backend, database)
- **Extended scope** - Would take more than a few days to complete
- **Multiple acceptance criteria** - Has several user stories or distinct outcomes
- **System-wide impact** - Affects multiple parts of the codebase

When a big task is detected, follow the Epic Creation Workflow below instead of creating a single issue.

## Epic Creation Workflow

When you identify a big task:

1. **Gather context using AskUserQuestion:**
   - What are the main goals or outcomes?
   - What user stories or acceptance criteria exist?
   - Are there dependencies between different parts?
   - What priority should the subtasks have?

2. **Create the Epic issue first** using the Epic Body Standards below

3. **Create individual subtask issues** linked to the Epic

4. **Apply appropriate labels** to Epic and all subtasks

### Epic Body Standards

Epic issues should follow this structure:

```markdown
## Overview
[1-2 sentence goal statement]

## User Stories / Acceptance Criteria
- [ ] As a [user], I want [feature] so that [benefit]
- [ ] [Additional criteria]

## Subtasks
- [ ] #XX - [Subtask title]
- [ ] #XX - [Subtask title]

## Dependencies
[Note any ordering requirements between subtasks]

## Milestone
[Target milestone or timeline if applicable]
```

### Subtask Linking

When creating subtasks for an Epic:

1. **Create each subtask** with `gh issue create`
2. **Reference the parent Epic** in each subtask body:
   ```markdown
   Part of #[epic-number]
   ```
3. **Update the Epic** after creating subtasks to add issue links to the Subtasks checklist
4. **Set appropriate issue type** (usually Task or Feature) for each subtask

## Labels

- **Security** - Security issue (#e11d48)
- **Performance** - Performance issue (#f59e0b)
- **Stability** - Stability issue (#8b5cf6)
- **Backend** - Backend work with Laravel/PHP (#3b82f6)
- **Frontend** - Frontend work with JavaScript/CSS (#06b6d4)
- **Database** - Work involving the database (#10b981)
- **Code Quality** - Code quality issue (#6366f1)
- **Upgrade** - Dependency upgrade work (#ec4899)
- **Critical** - Critical issue (#dc2626)
- **High** - High urgency issue (#ea580c)
- **Medium** - Medium urgency issue (#f59e0b)
- **Low** - Low urgency issue (#84cc16)

### Label Management Process

**IMPORTANT:** Before applying labels to an issue, you must ensure the labels exist in the repository. Follow this process:

1. **Check existing labels:**
   ```bash
   gh label list
   ```

2. **Create missing labels:** For each label you need that doesn't exist, create it with the appropriate color:
   ```bash
   gh label create "Security" --color "e11d48" --description "Security issue"
   gh label create "Performance" --color "f59e0b" --description "Performance issue"
   gh label create "Stability" --color "8b5cf6" --description "Stability issue"
   gh label create "Backend" --color "3b82f6" --description "Backend work with Laravel/PHP"
   gh label create "Frontend" --color "06b6d4" --description "Frontend work with JavaScript/CSS"
   gh label create "Database" --color "10b981" --description "Work involving the database"
   gh label create "Code Quality" --color "6366f1" --description "Code quality issue"
   gh label create "Upgrade" --color "ec4899" --description "Dependency upgrade work"
   gh label create "Critical" --color "dc2626" --description "Critical issue"
   gh label create "High" --color "ea580c" --description "High urgency issue"
   gh label create "Medium" --color "f59e0b" --description "Medium urgency issue"
   gh label create "Low" --color "84cc16" --description "Low urgency issue"
   ```

3. **Apply labels to the issue:** After ensuring labels exist, apply the appropriate ones:
   ```bash
   gh issue edit <issue-number> --add-label "Security,Backend,Critical"
   ```

**Apply labels based on issue content:**
- Security issues → Security, Critical (if P1), appropriate technical area (Backend/Frontend/Database)
- Performance issues → Performance, appropriate urgency label, appropriate technical area
- Stability issues → Stability, appropriate urgency label, appropriate technical area
- Code quality issues → Code Quality, appropriate technical area

## Issue Body Standards

### Bug Reports
1. Clear description of issue.
2. Steps to reproduce
3. Expected vs actual behavior
4. Proposed solution

### Feature Requests
1. Problem statement (What problem will this feature solve)
2. Proposed solution (How will it be implemented technically)
3. Tradeoffs (Any particular tradeoffs)
4. Affected areas (Areas of the codebase it will affect)

## IMPORTANT
- Never include "Generated with Claude Code"
- Never use title case for descriptions - use sentence case
- **Always follow the Label Management Process** - Check for existing labels, create missing ones, then apply appropriate labels to every issue you create
- When creating issues from the Future Work Checklist, map the priority (P1=Critical, P2=High, P3=Medium, P4=Low) and category (Security, Performance, Stability, Code Quality) to appropriate labels

