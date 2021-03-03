FROM centos:7

WORKDIR /data/www/schema

# https://github.com/athenarc/schema
RUN curl -LO "https://dl.k8s.io/release/$(curl -L -s https://dl.k8s.io/release/stable.txt)/bin/linux/amd64/kubectl" && \
  curl -LO "https://dl.k8s.io/$(curl -L -s https://dl.k8s.io/release/stable.txt)/bin/linux/amd64/kubectl.sha256" && \
  echo "$(<kubectl.sha256) kubectl" | sha256sum --check && \
  install -D -m 774 kubectl /usr/local/bin/ && \
  rm kubectl.sha256 kubectl

RUN yum install -y epel-release && \
    yum install -y http://rpms.remirepo.net/enterprise/remi-release-7.rpm && \
    yum update -y && \
    yum -y install python2 python2-pip python3-pip libpq-devel \
      php72 php72-php-mbstring php72-php-xml php72-php-gd php72-php-pgsql php72-php-json php72-php-pecl-yaml \
      zip unzip gcc python3-devel git && \
    ln -s /usr/bin/php72 /usr/bin/php

RUN pip2 install ruamel.yaml psycopg2-binary pyyaml requests && pip3 install rocrate cwltool

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

RUN composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic /data/www/schema && cd /data/www/schema && \
    composer require 2amigos/yii2-ckeditor-widget && \
    composer require webvimark/module-user-management && \
    composer require kartik-v/yii2-widget-datepicker "dev-master" && \
    composer require --prefer-dist yiisoft/yii2-bootstrap4 && \
    composer require --prefer-dist yiisoft/yii2-httpclient && \
    composer require alexantr/yii2-elfinder

# Schema uses 'sudo', this is a workarround to make it work. A more proper solution should be devised

#RUN useradd schema && yum install -y sudo nss_wrapper
#COPY entrypoint.sh /
COPY sudo /usr/local/bin

COPY . /data/www/schema

ENTRYPOINT ["php", "/data/www/schema/yii", "serve", "0.0.0.0:8080"]
