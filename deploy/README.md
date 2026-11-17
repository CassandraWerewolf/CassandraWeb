# Deploying CassandraWeb

**Every push to `main` is deployed to production automatically.** Other branches are never
built or deployed, so test changes in a dev VM (`docker compose up -d --build`) before merging.

## How it works

1. GitHub Actions (`.github/workflows/deploy.yml`) builds the image from `php/Dockerfile`,
   syntax-checks every PHP file, and publishes `cassandraproject/web:<commit sha>` and
   `cassandraproject/web:main` to Docker Hub.
2. It then connects over SSH as the **`deploy`** user. That user's key can only run
   `/usr/local/bin/cassy-deploy` (this folder's `cassy-deploy`), which accepts nothing but a
   40-character commit SHA.
3. `cassy-deploy` pulls that image and updates `cassandraweb_web` and `cassandraweb_scripts`
   one at a time. Swarm rolls a service back by itself if its new container fails. The script
   then checks the site answers `200`; if not, it rolls both services back.

Deploy history is kept in `/home/deploy/deploys.log` on the server.

## Manual deploy or rollback

On the server as root, deploy any published commit, for example the previous one:

```bash
sudo -u deploy /usr/local/bin/cassy-deploy <40-character commit sha>
```

## One-time setup (already done once; repeat on a new server)

On the server, as root:

```bash
# 1. Install the script (from a checkout of this repo).
install -m 755 deploy/cassy-deploy /usr/local/bin/cassy-deploy

# 2. A user that can run Docker but has no password (key login only).
adduser --disabled-password --gecos "" deploy
usermod -aG docker deploy

# 3. A key for GitHub Actions, allowed to run only cassy-deploy.
ssh-keygen -t ed25519 -N "" -C github-actions-deploy -f /root/github_deploy_key
install -o deploy -g deploy -m 700 -d /home/deploy/.ssh
printf 'restrict,command="/usr/local/bin/cassy-deploy" %s\n' "$(cat /root/github_deploy_key.pub)" \
  > /home/deploy/.ssh/authorized_keys
chown deploy:deploy /home/deploy/.ssh/authorized_keys
chmod 600 /home/deploy/.ssh/authorized_keys

# 4. The server's SSH identity, for GitHub to pin (prints one line).
printf '%s %s\n' "<server IP>" "$(cut -d' ' -f1,2 /etc/ssh/ssh_host_ed25519_key.pub)"
```

In GitHub, **Settings → Environments → New environment `production`**:
- *Deployment branches and tags*: **Selected branches** → add `main`.
- *Environment secrets*:
  - `DEPLOY_HOST`: the server IP
  - `DEPLOY_SSH_KEY`: the whole contents of `/root/github_deploy_key` (the private key)
  - `DEPLOY_KNOWN_HOSTS`: the line printed by step 4

**Settings → Secrets and variables → Actions → Repository secrets**:
- `DOCKER_USER` and `DOCKER_PASSWORD` (a Docker Hub **access token** with Read & Write).

Then delete the private key from the server: `rm /root/github_deploy_key`.

In Portainer, set the stack variable `CASSY_IMAGE` to `cassandraproject/web:main`, so
"Update the stack" uses the newest `main` build.
