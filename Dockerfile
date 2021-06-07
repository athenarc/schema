# Start with official yii2 image because it contains all dependencies

FROM yiisoftware/yii2-php:7.4-apache

# Install required packages

RUN apt update && apt install -y libyaml-dev \
    python3-ruamel.yaml \
    python3-psycopg2 \
    python3-yaml \
    python3-requests \
    python3-sklearn \
    python3-pip \
    apache2 \
    cwltool \
    git \
    zip \
    unzip \
    libxml2-dev \
    ftp sudo \
    lsb-release \
    postgresql-client

# Install python-yaml

RUN pecl install yaml
RUN docker-php-ext-enable yaml.so

# Install RO-crates using pip3
RUN pip3 install rocrate

# Install docker-tar-pusher using pip3
RUN pip3 install dockertarpusher

# Install kubectl
RUN apt update && apt install -y apt-transport-https ca-certificates curl

RUN curl -fsSLo /usr/share/keyrings/kubernetes-archive-keyring.gpg https://packages.cloud.google.com/apt/doc/apt-key.gpg

RUN echo "deb [signed-by=/usr/share/keyrings/kubernetes-archive-keyring.gpg] https://apt.kubernetes.io/ kubernetes-xenial main" | tee /etc/apt/sources.list.d/kubernetes.list

RUN apt update && apt install -y kubectl

# Create the web server folder and navigate to it
RUN mkdir /app/web

WORKDIR /app/web

# Update composer to version 2 because the image contains version 1
RUN composer self-update --2

# Install yii2
RUN composer create-project --prefer-dist yiisoft/yii2-app-basic schema

# Clone the schema repo and merge files to the app created in the previous tep
RUN git clone https://github.com/athenarc/schema.git schema_repo

COPY . /app/web/schema/

RUN rm -rf schema_repo

WORKDIR /app/web/schema

# Change the composer minimum stability setting
RUN sed -i "s|\"minimum-stability\": \"stable\"|\"minimum-stability\": \"dev\" |g" composer.json

# Change the default location for apache
RUN sed -i "s|DocumentRoot /app/web|DocumentRoot /app/web/schema/web |g" /etc/apache2/sites-available/000-default.conf

# Increase php post/file upload limit and restart apache
RUN sed -i "s|upload_max_filesize = 2M|upload_max_filesize = 50G |g" /usr/local/etc/php/php.ini-production

RUN sed -i "s|post_max_size = 8M|post_max_size = 50G |g" /usr/local/etc/php/php.ini-production

RUN sed -i "s|display_errors = Off|display_errors = On |g" /usr/local/etc/php/php.ini-production

RUN echo "error_log = /dev/stderr" >> /usr/local/etc/php/php.ini-production

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

# Since OpenShift annot listen to <1024, we'll use port 8080 (thanks Alvaro Gonzales!)
RUN sed -i "s|Listen 80|Listen 8080|" /etc/apache2/ports.conf
RUN sed -i "s|<VirtualHost \*:80>|<VirtualHost *:8080>|" /etc/apache2/sites-available/000-default.conf

RUN service apache2 restart

# Install required yii2 plugins
RUN composer require webvimark/module-user-management

RUN composer require kartik-v/yii2-widget-datepicker "dev-master"

RUN composer require --prefer-dist yiisoft/yii2-bootstrap4

RUN composer require --prefer-dist yiisoft/yii2-httpclient

RUN composer require alexantr/yii2-elfinder

RUN composer require 2amigos/yii2-ckeditor-widget

# # Install docker
# RUN curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# RUN echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/debian $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

# RUN cat /etc/apt/sources.list.d/docker.list

# RUN apt update && apt install -y docker-ce docker-ce-cli containerd.io

# Give apache permission to run python scripts
COPY schema_access_file /etc/sudoers.d/

# Add the inistialization script on the image
RUN mkdir /init

COPY ./config-init.sh /init/

#start apache2
CMD ["/bin/bash", "/init/config-init.sh"]

