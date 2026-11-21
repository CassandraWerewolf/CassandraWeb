# Cassandra Web

Cassandra Web is the website behind [cassandrawerewolf.com](https://cassandrawerewolf.com).
It supports games of **Werewolf** (a social-deduction game, also known as Mafia) that are played
in forum threads on [BoardGameGeek](https://boardgamegeek.com) (BGG).

Players and moderators use it to:

- find games, sign up, and schedule new ones
- see vote tallies, which are built automatically by reading posts from the BGG game threads
- run game chat, moderator tools, and automated moderation ("automod")
- browse player stats, rankings, and awards

## What's in this repo

| Path | What it is |
| --- | --- |
| `werewolf/` | The PHP web app (served by Apache). `index.php` is the home page. |
| `bin/` | Background scripts (mostly Perl and shell) that collect BGG posts, count votes, run automod, and update rankings. |
| `sql/` | Database schema (`werewolf_schema.sql`), stored routines and triggers (`werewolf_routines.sql` plus the individual files), and the place to put the data dump. |
| `php/` | The Docker image (`Dockerfile`) and Apache site configs for development and production. |
| `scripts/` | The cron schedule for the background jobs. |
| `deploy/` | The production deploy script and how deploys work. |
| `docker-compose.yml` | Local development setup. |
| `docker-compose.prod.yml` | Production stack. |

The stack is PHP 8.5 on Apache with MariaDB 12.3. The code was written for MySQL 5.6, so the
database runs in non-strict SQL mode to keep that older behavior.

## Running it locally

You need Docker (for example Docker Desktop or OrbStack).

1. **Get the data dump.** Ask the maintainer for a recent copy of `werewolf_data.sql`.
   It contains real user data, so it is git-ignored. Never commit it.
2. **Put it at `sql/werewolf_data.sql`.**
3. **Build and start:**

   ```bash
   docker compose up -d --build
   ```

   The first start creates the database and loads the schema, then the data, then the
   triggers. This takes a few minutes. You can watch progress with `docker compose logs -f db`.
4. **Open <http://localhost:8080>.**

Local development uses port 8080 (and 8443 for HTTPS) so it won't clash with anything else
using the standard ports on your machine. Production uses the standard ports 80 and 443. To use
a different local port, change the `ports` lines in `docker-compose.yml`.

Your edits to `werewolf/` and `bin/` take effect right away without a rebuild, because those
folders are mounted into the container.

### Common tasks

| Task | Command |
| --- | --- |
| Stop (keeps the database) | `docker compose down` |
| Reload the database from the dump (**deletes all local DB changes**) | `docker compose down -v && docker compose up -d` |
| Rebuild after changing `php/Dockerfile` | `docker compose up -d --build` |
| Start the BGG post collector | `docker compose --profile scripts up -d` |

The post collector does not start by default because it contacts BoardGameGeek. Start it only
when you need it.

## Contributing and deploying

1. Make your change on a branch and open a pull request against `main`.
2. Test it locally (see above) before asking for review. A code owner reviews every PR.
3. **Merging to `main` deploys to production automatically.** GitHub Actions builds the image,
   checks the syntax of every PHP file, publishes it to Docker Hub, and rolls it out to the server.
   Other branches are never deployed.

For how the deploy works, plus manual deploys and rollbacks, see
[deploy/README.md](deploy/README.md).

Found a bug or have an idea? [Open an issue](../../issues/new/choose).
