# Start with official yii2 image because it contains all dependencies

FROM yiisoftware/yii2-php:7.4-apache

RUN curl -fsSLo /usr/share/keyrings/kubernetes-archive-keyring.gpg https://packages.cloud.google.com/apt/doc/apt-key.gpg && \
    echo "deb [signed-by=/usr/share/keyrings/kubernetes-archive-keyring.gpg] https://apt.kubernetes.io/ kubernetes-xenial main" | tee /etc/apt/sources.list.d/kubernetes.list && \
# Install required packages
    apt update && \
    apt install -y libyaml-dev \
    python3-ruamel.yaml \
    python3-psycopg2 \
    python3-yaml \
    python3-requests \
    python3-sklearn \
    python3-pip \
    apache2 \
    cwltool \
    git \
    graphviz \
    zip \
    unzip \
    libxml2-dev \
    ftp sudo \
    lsb-release \
    postgresql-client \
    apt-transport-https \
    ca-certificates \
    curl \
    kubectl && \
# Install python-yaml
    pecl install yaml && \
    docker-php-ext-enable yaml.so && \
# Install RO-crates using pip3
# Install docker-tar-pusher using pip3
    pip3 install rocrate dockertarpusher && \
# Create the web server folder and navigate to it
    mkdir /app/web

WORKDIR /app/web

# Update composer to version 2 because the image contains version 1
RUN composer self-update --2 && \
# Install yii2
    composer create-project --prefer-dist yiisoft/yii2-app-basic schema

WORKDIR /app/web/schema

# Change the composer minimum stability setting
RUN sed -i "s|\"minimum-stability\": \"stable\"|\"minimum-stability\": \"dev\" |g" composer.json && \
# Change the default location for apache
    sed -i "s|DocumentRoot /app/web|DocumentRoot /app/web/schema/web |g" /etc/apache2/sites-available/000-default.conf && \
# Increase php post/file upload limit and restart apache
    sed -i "s|upload_max_filesize = 2M|upload_max_filesize = 50G |g" /usr/local/etc/php/php.ini-production && \
    sed -i "s|post_max_size = 8M|post_max_size = 50G |g" /usr/local/etc/php/php.ini-production && \
    sed -i "s|display_errors = Off|display_errors = On |g" /usr/local/etc/php/php.ini-production && \
    echo "error_log = /dev/stderr" >> /usr/local/etc/php/php.ini-production && \
    cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
# Since OpenShift annot listen to <1024, we'll use port 8080 (thanks Alvaro Gonzalez!)
    sed -i "s|Listen 80|Listen 8080|" /etc/apache2/ports.conf && \
    sed -i "s|<VirtualHost \*:80>|<VirtualHost *:8080>|" /etc/apache2/sites-available/000-default.conf

# Install required yii2 plugins
RUN composer require webvimark/module-user-management && \
    composer require kartik-v/yii2-widget-datepicker "dev-master" && \
    composer require --prefer-dist yiisoft/yii2-bootstrap4 && \
    composer require --prefer-dist yiisoft/yii2-httpclient && \
    composer require alexantr/yii2-elfinder && \
    composer require 2amigos/yii2-ckeditor-widget

# Give apache permission to run python scripts
COPY schema_access_file /etc/sudoers.d/

# Add the inistialization script on the image
RUN mkdir /init

COPY ./config-init.sh /init/

COPY . /app/web/schema/
RUN ln -s /data/docker/workflows-svg /app/web/schema/web/img/workflows

#start apache2
CMD ["/bin/bash", "/init/config-init.sh"]

