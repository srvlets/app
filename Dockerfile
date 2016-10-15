FROM alpine:3.4

RUN apk update \
 && apk upgrade

RUN apk add --no-cache ca-certificates
RUN apk add --no-cache openssl
RUN apk add --no-cache nginx \
 && mkdir /run/nginx

COPY  . /
WORKDIR /opt
    RUN composer install \
     && composer clear-cache

ENTRYPOINT ["/usr/local/bin/php", "/opt/init.php"]
    EXPOSE 80 443
