# Security tooling setup for CassandraWeb

This document has two parts:
- **Part A:** settings Todd turns on in GitHub's web interface.
- **Part B:** CI work to hand off to Claude Code.

Every tool used is free. Third-party tools in CI are all open source.

---

## Part A — GitHub settings (Todd, in the browser)

In the repo, go to **Settings → Advanced Security** (on some accounts it's called **Code security**).

| Setting | Action | Notes |
|---|---|---|
| Dependency graph | On | Needed for the items below. |
| Dependabot alerts | On | Warns you when a dependency has a known vulnerability. |
| Dependabot security updates | On | Opens fix PRs automatically. |
| Grouped security updates | On | One PR per ecosystem instead of dozens. |
| Secret scanning + push protection | On **if the repo is public** | Free for public repos only. If the repo is private, the `gitleaks` CI job in Part B covers this instead. |
| Private vulnerability reporting | On **if the repo is public** | Lets outsiders report bugs privately instead of in a public issue. |

**After Part B is merged:** go to **Settings → Rules** (or **Branches**), edit the rule for `main`, and add the `security` workflow's jobs as **required status checks**.

---

## Part B — CI implementation (hand this to Claude Code)

Start a Claude Code session in the repo, in **Plan mode**, and say:

> Implement Part B of docs/security/ci-security-setup.md. Show me the plan first.

### Goals

Add lightweight security checks that run on every PR, on pushes to `main`, and weekly. Keep the runtime under about 5 minutes. Don't block PRs on findings that existed before this was added; block only on new ones.

### Constraints

- Third-party tools must be **open source and free**. Use only the tools listed below unless you ask first.
- **Pin every action and tool to an exact version.** Pin GitHub Actions by full commit SHA, with the version in a comment. Look up current releases; don't guess version numbers.
- Workflow permissions: `contents: read` by default, and grant extra only per job if needed.
- Use the `pull_request` trigger, **not** `pull_request_target`, and don't expose secrets to PR jobs.
- Don't change the existing build or publish workflow, except where noted.

### 1. `.github/dependabot.yml`

Configure weekly **version updates** for:
- `composer` (repo root, or wherever `composer.json` lives)
- `docker` (each directory with a Dockerfile, plus the compose files if supported)
- `github-actions` (`/`)
- `pip`, if a `requirements.txt` exists

Group minor and patch updates per ecosystem to limit PR noise. Limit open PRs to 5 per ecosystem.

### 2. `.github/workflows/security.yml`

Triggers: `pull_request`, `push` to `main`, `schedule` (weekly), and `workflow_dispatch`.

| Job | Tool (license) | What it does | Fails the build when |
|---|---|---|---|
| `deps` | `composer audit` (built into Composer, MIT) | Checks PHP dependencies against known advisories | Any advisory found |
| `php-taint` | **Psalm** with `--taint-analysis` (MIT) | Traces user input into SQL, shell, HTML output, and file paths | A **new** issue not in the baseline |
| `trivy` | **Trivy** (Apache-2.0) | Scans the repo files, and the built web image, for vulnerable OS and language packages and misconfigurations | HIGH or CRITICAL findings that have a fix available (`--ignore-unfixed`) |
| `secrets` | **gitleaks** CLI (MIT) | Scans the PR's commits for leaked credentials | Any finding |

Notes:
- **Psalm:**
  - Annotate the `db_*` helpers in `werewolf/php/db.php` so Psalm knows their `$sql` parameter is an SQL sink (`@psalm-taint-sink sql $sql`). That way, string-built SQL passed to the helpers gets flagged.
  - Generate a baseline file (`psalm-baseline.xml`) so existing issues don't fail CI. Commit it, and list the baseline's top categories in the PR description so Todd can see the existing debt.
- **Trivy:** build the web image locally in the job (don't push it) and scan it. Upload results as SARIF to GitHub's Security tab **only if the repo is public**. Otherwise, print a table in the job log.
- **gitleaks:** run the CLI binary or the official container image directly. Don't use `gitleaks-action`, which requires a license key for organization accounts. On PRs, scan only the PR's commit range.

### 3. Also fix, while you're here

- If the existing publish workflow still uses `@master` or unpinned actions, pin them by SHA. (This was flagged during the rewrite.)
- Make sure the publish workflow can't overwrite the `:latest` image from a non-`main` branch.

### Done when

- A test PR shows all four jobs running and passing.
- A deliberately bad test commit on a throwaway branch fails the relevant jobs: for example, string-built SQL with `$_GET` input for Psalm, and a fake AWS-style key for gitleaks. Then delete that branch.
- `README` or `docs/security/` has a short note on how to run each check locally.
- Todd adds the jobs as required status checks (Part A, last step).
