# Quarterly security review — instructions for Claude Code

Run once per quarter. Start a new Claude Code session in the CassandraWeb repo, switch to **Plan mode**, and say:

> Follow docs/security/quarterly-review.md for this quarter.

Target effort: about one session of work. Keep findings short and actionable.

## Ground rules

- Work on a branch named `security/YYYY-qN`. Never commit to `main`, push, or deploy unless Todd asks.
- **Never run commands against prod yourself.** For server checks, write the read-only command, explain it, and let Todd run it and paste the output.
- Don't read `.env` files, keys, or credentials. If a check needs a secret, ask Todd.
- Label each recommendation **Required** (needed to stay secure; say why) or **Recommended** (optional; say what Todd gains).
- Put the results in `docs/security/reviews/YYYY-qN.md`, using the report template at the end.

## 1. Patch currency

Check the current versions, using a web search for official release pages, and compare them with what the repo pins:

- PHP 8.5.x base image (`php/Dockerfile`)
- MariaDB 12.3.x (compose files)
- Other images in the compose files (nginx, certbot, and so on)
- Composer packages (`composer outdated --direct`)
- GitHub Actions pinned by SHA in `.github/workflows/`

Propose one PR per area that bumps anything behind on a security release. Also list any component that is approaching end of life within the next 12 months.

## 2. Open findings

- Review the open Dependabot alerts and Dependabot PRs. Todd can paste them, or use `gh api` if available.
- Review the latest results of the `security` CI workflow, including the weekly scheduled run.
- Triage each finding as **fix now**, **fix this quarter**, or **accept with reason**.

## 3. New threats this quarter

Search for security advisories from the last 3 months that affect PHP, MariaDB, Apache, Debian, Docker, Portainer, or Cloudflare, plus any entries for these in CISA's Known Exploited Vulnerabilities catalog. For each one, say whether it affects Cassy and what to do.

## 4. ASVS spot-check (rotating chapters)

Check the code against **OWASP ASVS 5.0, Level 1 requirements only**, for this quarter's chapters:

| Quarter | ASVS 5.0 focus |
|---|---|
| Q1 | Encoding and sanitization; validation and business logic |
| Q2 | Authentication; session management |
| Q3 | Authorization (access control for players, moderators, admins); web frontend security |
| Q4 | Configuration; data protection; logging and error handling |

For each requirement: pass, fail, or not applicable, with file references for any failure. Propose fixes for failures as small PRs.

## 5. Dynamic scan

Bring up the stack locally (or use staging, if one exists) and run the OWASP ZAP baseline scan against it:

```
docker run --rm -t ghcr.io/zaproxy/zaproxy:stable zap-baseline.py -t <url>
```

Use the current image tag, and adjust the networking so the container can reach the site. Triage the warnings; ignore the informational ones.

## 6. Server and account checks (Todd runs, Claude explains)

Give Todd read-only commands, one at a time, to confirm:

- SSH: key-only login, root login disabled, and only expected keys in `authorized_keys`.
- Open ports: only 22, 80, and 443 reachable from the internet (`ss -tlnp` on the host, plus the Vultr firewall rules). The DB and Portainer must not be public.
- Image versions running in prod match the repo (`docker service ls`).
- TLS certificate expiry is more than 30 days away.
- **Backups:** the latest DB backup exists and a test restore into a local MariaDB succeeds. A backup that has never been restored doesn't count.
- GitHub: collaborators, deploy keys, and Actions secrets are all still needed. Docker Hub tokens too.

## Report template

```
# Security review YYYY-qN

## Summary
<3–5 bullets: overall state, anything urgent>

## Patch currency
| Component | Pinned | Latest | Action |

## Findings
| # | Finding | Severity | Required/Recommended | Status (PR link / accepted + reason) |

## ASVS chapters reviewed
<chapters, pass/fail counts, failures with file refs>

## Server checks
<each check: OK / issue>

## Next quarter
<anything deferred>
```
