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

---

## Running the checks locally

The `security` workflow ([.github/workflows/security.yml](../../.github/workflows/security.yml)) runs four checks on every PR. You can run the same checks yourself before you push. All you need is Docker (in the dev VM, for example) and a checkout of your branch. Don't use `~/cassy-test`, because that checkout belongs to `pr-test`.

Run everything from the repo's top folder. First, load the exact tool versions that CI uses. This line reads the pinned images from the workflow file, so the commands below always match CI:

```sh
eval "$(sed -nE 's/^[[:space:]]+((PHP|COMPOSER|TRIVY|GITLEAKS)_IMAGE): (.+)$/export \1=\3/p' .github/workflows/security.yml)"
```

### `deps`: known vulnerabilities in Composer packages

```sh
docker run --rm --user "$(id -u):$(id -g)" -e COMPOSER_HOME=/tmp -v "$PWD":/app -w /app \
  "$COMPOSER_IMAGE" composer audit --locked
```

The check passes when the output says `No security vulnerability advisories found`. Today `composer.json` only lists dev tools (Psalm); the app itself has no Composer packages.

### `php-taint`: user input reaching SQL, HTML, files, and so on

This check installs Psalm into `vendor/`, which git ignores, then runs the taint analysis. It takes about 30 seconds.

```sh
docker run --rm --user "$(id -u):$(id -g)" -e COMPOSER_HOME=/tmp -v "$PWD":/app -w /app \
  "$COMPOSER_IMAGE" composer install --no-scripts --no-interaction --no-progress
docker run --rm --user "$(id -u):$(id -g)" -v "$PWD":/app -w /app \
  "$PHP_IMAGE" php vendor/bin/psalm --taint-analysis --no-progress --no-cache
```

The check passes when the output says `No errors found!`. Any error it prints is a **new** issue, one that isn't in `psalm-baseline.xml`. Psalm prints the path the input takes, from `$_GET` or `$_POST` to where it's used.

**Where to look:** Psalm reports SQL issues at the *sink*, the place the query finally runs. That's usually `werewolf/php/db.php` or `werewolf/php/mysql_compat.php`, not the file you changed. So on a PR, the red annotation may not appear next to your change. Open the failed `php-taint` job's log instead. The path listed under the error shows your file and line, where the `$_GET`/`$_POST` value was put into the SQL.

- **SQL:** build queries with `db_query()` and `?` placeholders (see `werewolf/php/db.php`). String-built SQL fails this check, even when it uses `quote_smart()` or `mysql_real_escape_string()`. That's deliberate: escaping only protects quoted values.
- **HTML:** wrap output in `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`. For a value inside `<script>`, use `json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)`.

**Updating the baseline.** After a PR that fixes old issues, shrink the baseline so the fixed issues can't come back unnoticed:

```sh
docker run --rm --user "$(id -u):$(id -g)" -v "$PWD":/app -w /app \
  "$PHP_IMAGE" php vendor/bin/psalm --taint-analysis --no-progress --no-cache --update-baseline
```

`--update-baseline` only removes entries; it never adds new ones. Don't use `--set-baseline` to make a failing PR pass. That rewrites the baseline from scratch and would silently accept the new issue. Only regenerate it on purpose, review the diff, and explain the change in the PR.

When you're done, `rm -rf vendor` removes the installed tools.

### `trivy`: vulnerable packages and misconfigurations

This check scans the repo files, then builds the web image locally (it's never pushed) and scans it too. The image build takes a few minutes.

```sh
docker run --rm -v "$PWD":/src:ro -w /src "$TRIVY_IMAGE" fs \
  --scanners vuln,misconfig --severity HIGH,CRITICAL --ignore-unfixed \
  --ignorefile .trivyignore.yaml --exit-code 1 .
docker build --pull -f php/Dockerfile -t cassandraweb:security-scan .
docker run --rm -v "$PWD":/src:ro -w /src -v /var/run/docker.sock:/var/run/docker.sock:ro \
  "$TRIVY_IMAGE" image --scanners vuln --severity HIGH,CRITICAL --ignore-unfixed \
  --ignorefile .trivyignore.yaml --exit-code 1 cassandraweb:security-scan
docker rmi cassandraweb:security-scan
```

Both scans pass when the summary table shows `0` in every column (or `-` for "not scanned").

- **Image findings** are usually fixed in `php/Dockerfile`. A newer base image tag picks up fixes, and so does a rebuild, because the Dockerfile runs `apt-get upgrade`.
- **Accepting a finding** instead of fixing it means adding it to `.trivyignore.yaml`. Each entry needs a `statement` saying why it's safe, and an `expired_at` date no more than 90 days away. After that date Trivy reports the finding again, so it gets looked at in the quarterly review.

### `secrets`: credentials committed to git

This check scans the whole history of your checkout. It needs a real clone with its `.git` folder, not a copy of the files.

```sh
docker run --rm --user "$(id -u):$(id -g)" -v "$PWD":/repo:ro "$GITLEAKS_IMAGE" \
  git /repo --redact --no-banner --gitleaks-ignore-path /repo/.gitleaksignore
```

To scan only your branch's commits, as CI does for a PR, add `--log-opts="origin/main..HEAD"`.

The check passes when the output says `no leaks found`. `--redact` hides the secret values in the output.

- **If gitleaks finds a real credential**, removing it in a new commit isn't enough. The repo is public, so the old commit stays readable. **Revoke or rotate the credential first**, then add the finding's fingerprint to `.gitleaksignore`, with a comment saying what was rotated and when. A fingerprint has the form `<commit>:<file>:<rule>:<line>` and never contains the secret.
- **For a false positive** (sample data, test keys), add the fingerprint with a comment saying why it's safe.
