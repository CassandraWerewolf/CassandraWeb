# CassandraWeb image, used for both the `web` and `scripts` services.
# PHP 8.5.12 includes the fixes for CVE-2026-109618/109619 ("PHP2Shell").
# Keep the tag pinned to an exact version; never use a floating tag like php:8-apache.
FROM php:8.6.0-apache-trixie

# OS packages kept at runtime:
#  - mariadb-client(-compat): the `mariadb` and `mysql` command-line clients used by bin/*.sh
#  - Perl modules used by bin/*.pl (DBD::mysql from Debian is built against the MariaDB client library);
#    liblwp-protocol-https-perl lets LWP/WWW::Mechanize fetch https:// URLs (all BGG URLs are https)
#  - Python 3 libraries for bin/get_thread.py
#  - openssl, to create the self-signed dev certificate below
# Build-only packages, removed again at the end of this RUN:
#  - libfreetype-dev, libpng-dev, libjpeg62-turbo-dev: headers to compile the GD extension
#    (only tools/games2png uses GD); their runtime libraries are kept
#  - cpanminus, make: to install the String::Trigram Perl module (pure Perl)
#  - the base image's compilers ($PHPIZE_DEPS: gcc, g++, autoconf, make, ...)
# The running site never compiles anything. To add a PHP extension or a CPAN module later,
# install what it needs in the "build-only" step below, before the clean-up.
# The clean-up follows the official php image's pattern: mark everything "automatic", restore
# the packages that were wanted before the build step, keep every package that a shared
# library of PHP, its extensions or Apache's libphp.so links against, then purge the rest.
# OPcache is built into PHP 8.5, so it is not installed as a separate extension.
# `apt-get upgrade` applies Debian security fixes that are newer than the base image
# (the official image is only rebuilt every so often, e.g. Apache 2.4.71 in Nov 2026).
RUN apt-get update \
    && apt-get upgrade -y --no-install-recommends \
    && apt-get install -y --no-install-recommends \
        mariadb-client mariadb-client-compat \
        libdbd-mysql-perl libwww-mechanize-perl liblwp-protocol-https-perl \
        libxml-libxml-perl libjson-perl libswitch-perl \
        python3 python3-sqlalchemy python3-pymysql python3-dateutil \
        openssl \
    && savedAptMark="$(apt-mark showmanual)" \
    && apt-get install -y --no-install-recommends \
        cpanminus make \
        libfreetype-dev libpng-dev libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" mysqli gd \
    && cpanm --notest String::Trigram \
    && apt-mark auto '.*' > /dev/null \
    && apt-mark manual $savedAptMark > /dev/null \
    && apt-mark auto autoconf dpkg-dev file g++ gcc libc6-dev make pkg-config re2c > /dev/null \
    && find /usr/local /usr/lib/apache2/modules -type f \( -executable -o -name '*.so' \) -exec ldd '{}' ';' 2>/dev/null \
        | awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) { next }; gsub("^/(usr/)?", "", so); printf "*%s\n", so }' \
        | sort -u | xargs -r dpkg-query --search | grep -v '^diversion ' | cut -d: -f1 | sort -u \
        | xargs -r apt-mark manual > /dev/null \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
    && rm -rf /var/lib/apt/lists/* /root/.cpanm

# Apache: URL rewriting (used by .htaccess files) and TLS; hide version details.
RUN a2enmod rewrite ssl \
    && printf 'ServerTokens Prod\nServerSignature Off\n' > /etc/apache2/conf-available/zz-hardening.conf \
    && a2enconf zz-hardening

# PHP settings: production defaults plus a few overrides.
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf '%s\n' \
        '[PHP]' \
        'date.timezone = "America/Chicago"' \
        '; php/compat holds drop-in replacements for the old PEAR Cache_Lite and HTML_Table.' \
        'include_path = ".:/usr/local/lib/php:/var/www/html/php/compat"' \
        'expose_php = Off' \
        '; Not used by this app; disabling it removes PHP2Shell-related attack surface.' \
        'session.upload_progress.enabled = 0' \
        '; Session cookie: hidden from JavaScript, HTTPS only, not sent on cross-site' \
        '; requests; and only session ids the server created are accepted.' \
        'session.cookie_httponly = 1' \
        'session.cookie_secure = 1' \
        'session.cookie_samesite = Lax' \
        'session.use_strict_mode = 1' \
        > "$PHP_INI_DIR/conf.d/zz-cassandra.ini"

# Some scripts call /usr/bin/php; the official image installs PHP in /usr/local/bin.
RUN ln -s /usr/local/bin/php /usr/bin/php

# Self-signed certificate for local development only. In production the real
# Let's Encrypt certificates are mounted over /etc/letsencrypt, which hides this one.
RUN mkdir -p /etc/letsencrypt/live/cassandrawerewolf.com \
    && openssl req -x509 -newkey rsa:2048 -nodes -days 3650 \
        -subj "/CN=localhost" \
        -keyout /etc/letsencrypt/live/cassandrawerewolf.com/privkey.pem \
        -out /etc/letsencrypt/live/cassandrawerewolf.com/fullchain.pem

COPY ./php/sites /etc/apache2/sites-enabled
COPY ./bin /opt/werewolf
COPY ./werewolf /var/www/html

# Automod saves rulesets inside the web root, so Apache's user must be able to write there.
RUN chown -R www-data:www-data /var/www/html/automod/rulesets
