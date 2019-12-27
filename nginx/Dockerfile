FROM nginx:alpine

RUN apk add inotify-tools certbot openssl
WORKDIR /opt

COPY ./nginx/entrypoint.sh nginx-letsencrypt
COPY ./nginx/certbot.sh certbot.sh
COPY ./nginx/site.conf /etc/nginx/conf.d/default.conf
COPY ./nginx/ssl-options/ /etc/ssl-options
RUN chmod +x nginx-letsencrypt && \
    chmod +x certbot.sh
ENTRYPOINT [ "./nginx-letsencrypt" ]
