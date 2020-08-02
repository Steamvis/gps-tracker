# Table of contents

- [About Project](#About-Project)
- [Requirements](#Requirements)
- [Getting Started](#Getting-Started)
- [Docs](https://github.com/Steamvis/laravel-crm/tree/master/docs)
- [Clean Docker](#Clean-Docker)

# About Project
Dashboard with map GPS Tracking
```text
demo user
login: demo@demo.demo
password: demo
```

<img src="https://user-images.githubusercontent.com/20637799/87759387-e846a100-c816-11ea-999a-3d0450b00366.png">

[see more](ABOUT.md)

# Requirements
```text
Docker
Make - https://en.wikipedia.org/wiki/Make_(software) / https://habr.com/ru/post/211751/
```

:exclamation::exclamation::exclamation:[yandex map api key](https://developer.tech.yandex.ru/services/)

# Getting Started 

1. Download project
   ```bash
   git clone git@github.com:Steamvis/laravel-crm.git PATH
   ```

2. in project folder :open_file_folder: ​run the command from the command line, which will install the project in a Docker container using a [Makefile](Makefile)
   ```bash
   make init
   ```

   installation ~15-20 min* :coffee:

3. Edit env file and add [yandex-map-api code](https://developer.tech.yandex.ru/services/) :earth_americas:

4. enable queue
   ```bash
   make queue-on
   ```

If you want to use simple email-verify, you can use [mailtrap](https://mailtrap.io/)

![mailtrap-api](https://user-images.githubusercontent.com/20637799/87759490-11ffc800-c817-11ea-8e4a-799e998bcfdb.png)

# Make commands :hammer:

```bash
make init - install project
```

```bash
make build - build docker
```

```bash
make up - launch a project
```

```bash
make down - stop project and remove Docker containers
```

```bash
make queue-on - enable queue worker
```

```bash
make composer-install-prestissimo - https://github.com/hirak/prestissimo
```

```bash
make composer-install-app - install laravel app
```

```bash
make set-storage-link - sets the links to the storage
```

```bash
make migrate - starts the migration and add demo user
```

```bash
make npm-install - install webpack
```

```bash
make npm-prod - will compile js,css and copy images
```

```bash
make copy-env - will copy the file with basic data
```

```bash
make clear-logs - clear logs
```

```bash
make clear-cache - clear app cache
```

```bash
make dump-autoload - will make classes autoloading
```



# Clean Docker

if you want to clean docker images

**php**

```bash
docker rmi phpdocker/php-fpm
```

**redis**

```bash
docker rmi redis:alpine
```

**nginx**

```bash
docker rmi nginx:alpine
```

**node**

```bash
docker rmi node:14.5.0-slim
```
