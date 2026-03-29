---
name: verify-code
description: Executes code quality and test commands (`check` and `ci`) and helps the user fix any resulting errors. Use this skill when the user asks to verify the code, run tests, check for errors, or ensure CI passes.
---

# Verify Code

When the user asks to verify, check, or test the code, follow this workflow:

## 1. Execution

1.  First, ask the user if they want to run a specific command, or if you should default to the standard checks.
2.  The standard checks for this project are typically run via Composer scripts. The default workflow should be:
    *   Run `composer run check` (Usually handles static analysis, typing, and style formatting).
    *   If `check` passes, run `composer run ci` (Usually handles the automated test suite).
3.  **Strict Code Coverage (CRITICAL):** Even if `composer run ci` passes, you MUST verify the test coverage. If the `ci` command doesn't output coverage, you must manually run `vendor/bin/pest --coverage --min=97`. 
    *   If the overall coverage is below 97%, **you must treat it as a failure**. Inform the user that coverage dropped below the acceptable threshold and offer to identify which classes are lacking tests.

## 2. Handling the Output

*   **If both commands pass successfully:** Congratulate the user briefly and inform them the code is ready for a Pull Request.
*   **If any command FAILS:**
    1.  **Do NOT panic or stop.** Capture the standard output and error output.
    2.  **Analyze:** Briefly explain to the user *why* it failed (e.g., "PHPStan found a type mismatch in MediatorService.php", "Pest test 'ActionDecoratorManagerTest' failed asserting that true is false").
    3.  **Propose a Fix:** If the error is clear, use the `read_file` or `grep_search` tools to look at the problematic code, formulate a solution, and ask the user if they want you to apply the fix using the `replace` tool.
    4.  **Iterate:** Once fixed, offer to run the failing command again to verify the fix.
