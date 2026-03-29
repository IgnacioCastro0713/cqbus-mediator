---
name: create-pr
description: Automatically generates and submits well-formatted GitHub Pull Requests in English. Use this skill when the user asks to create a PR, open a pull request, or submit their branch for review.
---

# Create Pull Request

When the user asks to create a Pull Request, follow these steps strictly:

## 1. Information Gathering

1.  **Pre-flight Verification (CRITICAL):** Before doing anything else, ask the user: *"Have you run the code verifications (`composer run check / ci`) for these changes, or do you want me to create the PR right away?"* 
    *   If they want to verify first, suggest they use the `verify-code` skill or let you run the commands for them. Wait for their confirmation to proceed.
    *   If they say yes/proceed, continue to the next steps.
2.  **Branch Check:** Identify the current branch using `git branch --show-current`. This will usually be the `<head>` of the PR.
2.  **Target Branch:** Identify the target branch (typically `main` or `master`).
3.  **Commit Review:** Review the commits that will be included using `git log main..HEAD --oneline` to understand the scope of the changes.
4.  **Issue Linkage (CRITICAL):** You MUST determine the relationship between this PR and existing repository issues. The situation could be:
    *   Linked to a **single issue** (e.g., `#1`).
    *   Linked to **multiple issues** (e.g., `#1`, `#2`, `#5`).
    *   A brand **new feature/fix with NO related issue**.
    *   A **mix** (closes an issue, but also adds unrelated standalone features).
    
    *If the user hasn't explicitly stated the issue numbers or the nature of the PR in their prompt, **you must ask them to clarify** before creating the PR.*

## 2. Formatting the PR

*   **Language:** The PR title and body MUST be entirely in **English**.
*   **Title:** Use the Conventional Commits format for the PR title (e.g., `feat: Add new routing mechanism`, `fix: Resolve pipeline bug`, `docs: Update installation guide`). Do NOT use emojis in the title.
*   **Body Template:** Use the following markdown structure for the PR description. Adapt the "Related Issues" section based on the information gathered in step 1.

```markdown
## Description
[Provide a clear, 1-3 sentence explanation of the problem being solved or feature being added. Why is this PR needed?]

### Changes Included
*   **[Area 1]:** [Specific change 1]
*   **[Area 2]:** [Specific change 2]

### Related Issues
[Use one of the following formats based on the situation:]
*   [If single issue:] Closes #1
*   [If multiple issues:] Closes #1, Closes #2, Resolves #5
*   [If mixed:] Closes #1. Also includes standalone enhancements for [Feature X].
*   [If NO issue:] *No related issues.* (Or you may completely omit this section).
```

## 3. Execution

1.  **Create the PR:** Use the GitHub CLI to create the PR directly:
    ```powershell
    gh pr create --base <target_branch> --head <current_branch> --title "<Formatted Title>" --body "<Formatted Body>"
    ```
2.  **Drafts:** If the user specifies they want a "draft" PR, append the `--draft` flag to the command.
3.  **Success:** Once created, return the URL of the newly created Pull Request to the user.
