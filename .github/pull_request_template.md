## What this PR does

<!-- One or two sentences. -->

## Security review (reviewer: ~5 minutes)

Check each box, or write "n/a" next to it. Anything unchecked needs a comment explaining why.

**Automated checks**
- [ ] The `security` CI workflow passed. Any new finding is fixed, or explained and accepted below.
- [ ] I ran `/security-review` in Claude Code on this branch and addressed or dismissed each finding.

**Code**
- [ ] **SQL:** every query that includes outside data uses the `db_*` helpers with `?` placeholders. No SQL is built with `.` or `"{$var}"` from request input.
- [ ] **Output:** user-supplied data shown in HTML goes through `htmlspecialchars()` (or the project's escape helper).
- [ ] **Shell:** no `system`, `exec`, `shell_exec`, `passthru`, `proc_open`, `popen`, or backticks. If one is truly needed, it never receives user input.
- [ ] **Access control:** new pages and actions check, on the server, that the user is logged in and has the right role (player, moderator, admin) for *this* game or record.
- [ ] **State changes** (create, edit, delete, vote) happen only via POST, never GET.

**Secrets, files, and dependencies**
- [ ] No passwords, tokens, keys, or `.env` contents in the diff.
- [ ] No debug, test, demo, or `phpinfo` pages added to the web root.
- [ ] New libraries or images are needed, actively maintained, pinned to a version, and the lockfile is updated.

## Notes for accepted risks

<!-- If you accepted a finding, say what it is and why. -->
