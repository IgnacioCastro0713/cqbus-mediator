---
name: release-version
description: Automatically generates high-quality, informative GitHub release notes in English, using a categorized format (Features, Bug Fixes, Under the Hood). Use this skill when the user asks to create a release, tag a new version, or write release notes for the repository.
---

# Release Version

When the user asks to create a new release or write release notes for a specific version, follow these instructions strictly to ensure the highest quality.

## Information Gathering

1.  **Identify Changes:** Run `git log <previous_tag>..HEAD --oneline` (or similar) to understand what commits are included in the new release.
2.  **Pull Request Info:** If there are merged pull requests (e.g., `#1`), check their titles and descriptions using `gh pr view <number>` to get detailed context.
3.  **Confirm Target Version:** Determine the new version tag (e.g., `v6.1.0`) and the target branch (usually `main`).

## Release Notes Formatting

Write the release notes exclusively in **English**. Use the following structured Markdown format with emojis.

```markdown
## What's New in [Version Tag] 🚀

[Brief 1-2 sentence summary of the primary focus or theme of this release.]

### ✨ Features & Enhancements

*   **[Feature Name]:** [Description of what it does and why it's useful.]
*   **[Another Feature]:** [Description.]

### 🐛 Bug Fixes

*   **[Fix Name]:** [Description of the issue that was fixed and its impact.]

### ⚙️ Under the Hood

*   [Mention internal refactors, test additions, or dependency upgrades that don't directly affect the public API but are important context.]
*   [Another technical detail.]

**Full Changelog**: https://github.com/[Owner]/[Repo]/commits/[Version Tag]
```

## Creating the Release

1.  **Draft the Notes:** Always draft the notes to a temporary file (e.g., `release_notes.md`).
2.  **Create the GitHub Release:** Use the GitHub CLI to create the release:
    ```powershell
    gh release create <version_tag> --target <branch> --title "<version_tag>: <short summary>" --notes-file release_notes.md
    ```
    *(If the user just wants the text and not the creation, output the markdown directly instead.)*
3.  **Clean up:** Remove the temporary `release_notes.md` file after successful creation.

## Important Considerations

*   **No Spanglish:** The user explicitly requests these to be strictly in English for a wider audience, even if the conversation is in Spanish.
*   **Actionable Links:** Always include the "Full Changelog" link pointing to the exact tag commits.
*   **Professional yet Friendly Tone:** Use the designated emojis to make the notes easily scannable.
