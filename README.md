# Table of contents

- [About](#About)
- [Requirements](#Requirements)
- [Getting-Started](#Getting-Started)
- [Docs](https://github.com/Steamvis/laravel-crm/tree/master/docs)
- [Clean-Docker](#Clean-Docker)

# About

Dashboard with map GPS Tracking.

```
demo user
login: demo@demo.demo
password: demo
```

<img src="https://camo.githubusercontent.com/fb5cd21f6a8f4f3336ff0fc53d4970dadf227184/68747470733a2f2f73313134766c612e73746f726167652e79616e6465782e6e65742f726469736b2f313833613634393864616136336330313935366166663439616632393339346535386263643262383461613561383138663162383465626432333561656431302f35663130363635342f754b4a5557334748694955465472724b39583655474e4e50593645556341446b50386b6846675a5647335532786c506444466879394d4a626b5f36726d6d62645873705a4266566b6f74577670302d37316d6a7569673d3d3f7569643d3337383632313239312666696c656e616d653d6c616e642e706e6726646973706f736974696f6e3d696e6c696e6526686173683d266c696d69743d3026636f6e74656e745f747970653d696d616765253246706e6726746b6e763d7632266f776e65725f7569643d33373836323132393126657461673d3432643764393034316365376164343737623130343630636136323334613364266d656469615f747970653d696d616765266869643d3239346533373637376462633430636638373836373534383930373565343233266673697a653d3534333937392672746f6b656e3d564f436930613265314a506426666f7263655f64656661756c743d7965732679637269643d6e612d39383463396531393334336466323463373734643463366332336365353731342d646f776e6c6f616465723231662674733d3561613866666436373364303026733d623533393634646532633238323930376631366430313536316261303261373763666461666264323664376665643834306130353538363136623838653562362670623d553246736447566b5831382d34396e4d665745446276784a4d444836433646757346353033416e4c43796b4a34656b66465746587866444a5153616e333972545f7971636c49546c456e4b5f7a6e6b3730644e4b6766567642426541394c4c757855676878342d76335a6b">

<img src="https://s695sas.storage.yandex.net/rdisk/6d2e0bc4cef7e01dd99b9bd234b3b5a3a30e56f94c19cdda2944fcc1b7242ec5/5f10a2fb/uKJUW3GHiIUFTrrK9X6UGMY2Bseyc0ihNS3BFM5o3RY6vYVQemStj-q4-gtLKkkgRCf6vYNvSquMB50D1MPWeQ==?uid=378621291&filename=map.second.gif&disposition=inline&hash=&limit=0&content_type=image%2Fgif&tknv=v2&owner_uid=378621291&etag=edde7653cb951098d23ed8209f30641f&fsize=3872859&media_type=image&hid=73bbf8329c67a9fe817c12bd1c0b6363&rtoken=nEezoeZ8cddB&force_default=yes&ycrid=na-0c2b80f3e3192cb0c12fc263f8c49cc3-downloader17h&ts=5aa939ae274c0&s=101769eabf07fe74c01e4791ccf2f8c40ec1034de27585a3875d15551b856b2f&pb=U2FsdGVkX1_Ip5UgvTSyQ2x9fMSzoR1onHLcOPWCIq7kFLSoUyNYQejGn6rH-FdhP-DD1Oxw7zMoDf2bJuNau7zqllkVj7hV_0OQs8PQAM8">

At system are being built routes and calculated data

- time in moving,
- time a moving on segment,
- distance between segments
- route distance

Geo-points are added through [API](https://github.com/Steamvis/laravel-crm/blob/master/src/app/Http/Controllers/Api/MapController.php) ([docs](https://github.com/Steamvis/laravel-crm/blob/master/docs/api.md)). 

You can create routes, adds points, end routes

<img src="https://s122vla.storage.yandex.net/rdisk/2e607fbeebcece9017820acfaa3177687e9e322d5d99d9bed6b52b71175fb5d5/5f10a335/uKJUW3GHiIUFTrrK9X6UGDzt3wQ3BI9br3ZYUUqAK9KJVXMizNgJ0Ff5phdVaMz5tiRJPc5luLSQ5DSLK_oUyA==?uid=378621291&filename=map-api.gif&disposition=inline&hash=&limit=0&content_type=image%2Fgif&tknv=v2&owner_uid=378621291&fsize=3174414&hid=bdc17f2cbe2e94947c7703bb6ced2912&etag=82d9f8ab192a58daf7a7365de18f8f8f&media_type=image&rtoken=vkI7S5Do7rfx&force_default=yes&ycrid=na-98e53ff57a47bf4f345a83b70a919784-downloader17h&ts=5aa939e577740&s=80b5113b623a87ab81e5f8b9637a169ae5b93d472155466ae28b4e8d787dce94&pb=U2FsdGVkX19rg2e0eCx38ru4GTfyZWuZ9Tn2rWDzsUl1a8B749204XxVJISbFBEZAT4cWCc3PDcddU88tdoqRPr0dVdEC5xAaxaekhpL8Ik">

In the panel, you can create/edit transport, upload photo, watch connected/disconnected transport

<img src="https://s298sas.storage.yandex.net/rdisk/29947ad13e7d1b646600224e43c7f19074ba85fce8f594dd757bc923f8315af1/5f10a2c0/uKJUW3GHiIUFTrrK9X6UGME3ehJmunNbF8mTW33_ixCRVGl07Ldh6KKDkv04tLnzait7RYs4enaj0AkEnm6s_Q==?uid=378621291&filename=second.gif&disposition=inline&hash=&limit=0&content_type=image%2Fgif&tknv=v2&owner_uid=378621291&etag=741940e3797344941ff38b54d7a62ac4&media_type=image&hid=05ab12636f6b328a6cae580f749783e0&fsize=4894908&rtoken=M8Bb210XlGkj&force_default=yes&ycrid=na-fc39cc5b8772e78a34ed4413fcb592bd-downloader17h&ts=5aa93975e3000&s=1278b798f545fbb7fedab93949df95287b61634d23ff316060e63220d67001f8&pb=U2FsdGVkX1_zwBJv4KOSEcX8mEJZh9ubDCXF2_unEdaPP-4IuxTTYNp4N1Y1XyyqLJJN2itidpHAPMVQmK3XC-2HRrt46_GU4kdIaUX0fMQ">

# Requirements

```text
Docker
Make - https://en.wikipedia.org/wiki/Make_(software) / https://habr.com/ru/post/211751/
```

[yandex map api key](https://developer.tech.yandex.ru/services/)

# Getting Started 

1. Download project

   ```bash
   git clone git@github.com:Steamvis/laravel-crm.git PATH
   ```

2. in project folder run the command from the command line, which will install the project in a Docker container using a [Makefile](https://github.com/Steamvis/laravel-crm/blob/master/Makefile)

   ```
   make init
   ```

   installation ~3-6 min*

3. Edit env file and add yandex-map-api code

If you want to use simple email-verify, you can use [mailtrap](https://mailtrap.io/)

![](https://camo.githubusercontent.com/7fb495fbcf0403d9d3ef66e06ba47731d68adcea/68747470733a2f2f733537387361732e73746f726167652e79616e6465782e6e65742f726469736b2f656365623833383466346366643135643939393539646339393565323363326261613265633464323964373135646364303536643231383536663230393030632f35663130363436652f754b4a5557334748694955465472724b39583655474d4663346c73385078622d757272724973714674427351756662544c67385358634949474f43553836334e7a394a62387a72796952706d774e39327a72786570413d3d3f7569643d3337383632313239312666696c656e616d653d6d61696c747261702d6170692e706e6726646973706f736974696f6e3d696e6c696e6526686173683d266c696d69743d3026636f6e74656e745f747970653d696d616765253246706e6726746b6e763d7632266f776e65725f7569643d333738363231323931266d656469615f747970653d696d616765266673697a653d3338303133266869643d313030386137653139363239393063663961353535656166626335313136336226657461673d63633061616132643335646336616566383336336632626437616135613661392672746f6b656e3d42746d4b796f7751554c726a26666f7263655f64656661756c743d7965732679637269643d6e612d30633164373764393535306435316233323861313136343266333462316130342d646f776e6c6f616465723134652674733d3561613866653036303335343026733d386334393962643032366463333738343865623636633562326166316534363765666139393637363533663832633837363366633765646531656136393238632670623d553246736447566b58312d436d5a6b6e664564433834673832333032427a38636d36304d79732d6d5f502d4f504d7154594f5337794b6238585563444e35334c72656a504c6e4c666e58424e77657277506830754977467a657052434f5f74627536496437364761766c34)

# Make commands

```
init - install project
```

```
build - build docker
```

```
up - launch a project
```

```
down - stop project and remove Docker containers
```

```
queue-on - enable queue worker
```

```
composer-install-prestissimo - https://github.com/hirak/prestissimo
```

```
composer-install-app - install laravel app
```

```
set-storage-link - sets the links to the storage
```

```
migrate - starts the migration and add demo user
```

```
npm-install - install webpack
```

```
npm-prod - will compile js,css and copy images
```

```
copy-env - will copy the file with basic data
```

```
clear-logs - clear logs
```

```
clear-cache - clear app cache
```

```
dump-autoload - will make classes autoloading
```



# Clean Docker

if you want to clean docker images

**php**

```
docker rmi phpdocker/php-fpm
```

**redis**

```
docker rmi redis:alpine
```

**nginx**

```
docker rmi nginx:alpine
```

**node**

```
docker rmi node:14.5.0-slim
```