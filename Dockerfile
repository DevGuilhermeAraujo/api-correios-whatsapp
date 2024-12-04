# Usar a imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instalar as dependências necessárias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    cron \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql \
    && a2enmod rewrite

# Instalar o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar o código da aplicação para o diretório apropriado no container
COPY . /var/www/html/
WORKDIR /var/www/html/public

# Configuração do cron para rodar os scripts
RUN echo "*/15 6-19 * * * www-data php /var/www/html/public/apiCall.php >> /var/log/cron_apiCall.log 2>&1" > /etc/cron.d/agendador
RUN echo "*/15 15-17 * * * www-data php /var/www/html/public/enviarDados.php >> /var/log/cron_enviarDados.log 2>&1" >> /etc/cron.d/agendador
RUN chmod 0644 /etc/cron.d/agendador
RUN crontab /etc/cron.d/agendador

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html/ && chmod -R 755 /var/www/html/

# Expor a porta 80
EXPOSE 80

# Comando para iniciar o Apache e o Cron
CMD service cron start && apache2-foreground
