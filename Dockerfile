FROM php:8.2-apache
# NVİ bağlantısı için gerekli kütüphaneleri yüklüyoruz
RUN apt-get update && apt-get install -y libxml2-dev && docker-php-ext-install soap
# Dosyaları sunucuya kopyalıyoruz
COPY . /var/www/html/
# Portu dışarı açıyoruz
EXPOSE 80
