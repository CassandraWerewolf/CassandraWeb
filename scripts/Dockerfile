# Use a Python 3 slim base
FROM python:3.11-slim-bullseye

ENV DEBIAN_FRONTEND=noninteractive

# Install system dependencies and Perl runtime/modules
RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		ca-certificates \
		wget \
		curl \
		bash \
		build-essential \
		pkg-config \
	default-mysql-client \
	libmariadb-dev-compat \
		perl \
		libdbi-perl \
		libdbd-mysql-perl \
		libwww-perl \
		libxml-libxml-perl \
		libxml2-dev \
		cpanminus \
	&& rm -rf /var/lib/apt/lists/*

# Python (3) dependencies used by get_thread.py
RUN pip3 install --no-cache-dir SQLAlchemy python-dateutil PyMySQL

# Install any CPAN modules not provided by distro packages
RUN cpanm --notest --quiet String::Trigram || true

# Create application directory and copy repository into image
WORKDIR /opt/werewolf
# Only copy the runtime scripts (bin) into the image
COPY ./bin /opt/werewolf

# Make sure bin scripts are executable
RUN chmod -R a+x /opt/werewolf/bin || true
