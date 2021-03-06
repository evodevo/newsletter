FROM alpine

RUN \
    apk update \
    \
    # install php
    && apk add php7 \
    && apk add php7-apcu \
    && apk add php7-ctype \
    && apk add php7-curl \
    && apk add php7-dom \
    && apk add php7-fileinfo \
#    && apk add php7-ftp \
    && apk add php7-iconv \
    && apk add php7-intl \
    && apk add php7-json \
    && apk add php7-mbstring \
    && apk add php7-mcrypt \
    && apk add php7-mysqlnd \
    && apk add php7-opcache \
    && apk add php7-openssl \
    && apk add php7-pdo \
#    && apk add php7-pdo_sqlite \
    && apk add php7-phar \
    && apk add php7-posix \
    && apk add php7-session \
    && apk add php7-simplexml \
#    && apk add php7-sqlite3 \
    && apk add php7-tokenizer \
    && apk add php7-xml \
#    && apk add php7-xmlreader \
#    && apk add php7-xmlwriter \
#    && apk add php7-zlib \
    \
    # install php-fpm
    && apk add php7-fpm \
    # make php-fpm listen to not tcp port but unix socket
    && sed -i -E "s/127\.0\.0\.1:9000/\/var\/run\/php-fpm\/php-fpm.sock/" /etc/php7/php-fpm.d/www.conf \
    && mkdir /var/run/php-fpm \
    \
    # install nginx and create default pid directory
    && apk add nginx \
    && mkdir -p /run/nginx \
    \
    # forward nginx logs to docker log collector
    && sed -i -E "s/error_log .+/error_log \/dev\/stderr debug;/" /etc/nginx/nginx.conf \
    && sed -i -E "s/access_log .+/access_log \/dev\/stdout main;/" /etc/nginx/nginx.conf \
    \
    # install supervisor
    && apk add supervisor \
    && mkdir -p /etc/supervisor.d/ \
    \
    # remove caches to decrease image size
    && rm -rf /var/cache/apk/* \
    \
    # install composer
    && EXPECTED_SIGNATURE=$(wget -q -O - https://composer.github.io/installer.sig) \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === '$EXPECTED_SIGNATURE') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

ENV PHP_INI_DIR /etc/php7
ENV NGINX_CONFD_DIR /etc/nginx/conf.d

COPY docker/php.ini $PHP_INI_DIR/
# COPY nginx.conf $NGINX_CONFD_DIR/default.conf
COPY docker/nginx.conf $NGINX_CONFD_DIR/newsletter.conf
COPY docker/default.conf $NGINX_CONFD_DIR/default.conf
COPY docker/supervisor.programs.ini /etc/supervisor.d/
COPY docker/start.sh /

RUN \
    # add non-root user
    # @see https://devcenter.heroku.com/articles/container-registry-and-runtime#run-the-image-as-a-non-root-user
    adduser -D nonroot nonroot \
    \
    # followings are just for local environment
    # (on heroku dyno there is no permission problem because most of the filesystem owned by the current non-root user)
    && chmod a+x /start.sh \
    \
    # to update conf files and create temp files under the directory via sed command on runtime
    && chmod -R a+w /etc/php7/php-fpm.d \
    && chmod -R a+w /etc/nginx \
    \
    # to run php-fpm (socker directory)
    && chmod a+w /var/run/php-fpm \
    \
    # to run nginx (default pid directory and tmp directory)
    && chmod -R a+w /run/nginx \
    && mkdir /var/tmp/nginx \
    && chmod -R a+wx /var/tmp/nginx \
    \
    # to run supervisor (read conf and create socket)
    && chmod -R a+r /etc/supervisor* \
    && sed -i -E "s/^file=\/run\/supervisord\.sock/file=\/run\/supervisord\/supervisord.conf/" /etc/supervisord.conf \
    && mkdir -p /run/supervisord \
    && chmod -R a+w /run/supervisord \
    \
    # to output logs
    && chmod -R a+w /var/log \
    \
    # add nonroot to sudoers
    && apk add --update sudo \
    && echo "nonroot ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

RUN \
    # install npm
    apk --update add nodejs-npm \
    \
    # remove caches to decrease image size
    && rm -rf /var/cache/apk/*

ENV YARN_VERSION 1.22.0

RUN apk add --no-cache --virtual .build-deps-yarn curl gnupg tar \
  && for key in \
    6A010C5166006599AA17F08146C2130DFD2497F5 \
  ; do \
    gpg --batch --keyserver hkp://p80.pool.sks-keyservers.net:80 --recv-keys "$key" || \
    gpg --batch --keyserver hkp://ipv4.pool.sks-keyservers.net --recv-keys "$key" || \
    gpg --batch --keyserver hkp://pgp.mit.edu:80 --recv-keys "$key" ; \
  done \
  && curl -fsSLO --compressed "https://yarnpkg.com/downloads/$YARN_VERSION/yarn-v$YARN_VERSION.tar.gz" \
  && curl -fsSLO --compressed "https://yarnpkg.com/downloads/$YARN_VERSION/yarn-v$YARN_VERSION.tar.gz.asc" \
  && gpg --batch --verify yarn-v$YARN_VERSION.tar.gz.asc yarn-v$YARN_VERSION.tar.gz \
  && mkdir -p /opt \
  && tar -xzf yarn-v$YARN_VERSION.tar.gz -C /opt/ \
  && ln -s /opt/yarn-v$YARN_VERSION/bin/yarn /usr/local/bin/yarn \
  && ln -s /opt/yarn-v$YARN_VERSION/bin/yarnpkg /usr/local/bin/yarnpkg \
  && rm yarn-v$YARN_VERSION.tar.gz.asc yarn-v$YARN_VERSION.tar.gz \
  && apk del .build-deps-yarn \
  # smoke test
  && yarn --version

ENV DOCROOT /docroot
WORKDIR $DOCROOT

RUN chown -R nonroot:nonroot $DOCROOT
# && chmod -R a+w $DOCROOT

USER nonroot

# copy application code
COPY --chown=nonroot:nonroot / $DOCROOT/

# tweak to set env to prod, and re-do composer install
RUN sed -i -E "s/APP_ENV=dev/APP_ENV=${APP_ENV:-prod}/" .env

RUN composer install --no-interaction
RUN yarn install --non-interactive
RUN yarn build
    # && chmod -R a+w $DOCROOT

VOLUME ["$DOCROOT/data"]

USER root

CMD ["/start.sh"]

