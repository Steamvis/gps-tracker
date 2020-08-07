# Table of contents

- [About Project](#About-Project)
- [Requirements](#Requirements)
- [Getting Started](#Getting-Started)
- [Docs](./docs/)
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
