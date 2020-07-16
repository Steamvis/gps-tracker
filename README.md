# Table of contents

- [About](#About)
- [Requirements](#Requirements)
- [Getting Started](#Getting Started)
- [Docs](https://github.com/Steamvis/laravel-crm/tree/master/docs)
- [Clean docker](#Clean Docker)

# About

Dashboard with map GPS Tracking.

```
demo user
login: demo@demo.demo
password: demo
```

![](https://s114vla.storage.yandex.net/rdisk/183a6498daa63c01956aff49af29394e58bcd2b84aa5a818f1b84ebd235aed10/5f106654/uKJUW3GHiIUFTrrK9X6UGNNPY6EUcADkP8khFgZVG3U2xlPdDFhy9MJbk_6rmmbdXspZBfVkotWvp0-71mjuig==?uid=378621291&filename=land.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&etag=42d7d9041ce7ad477b10460ca6234a3d&media_type=image&hid=294e37677dbc40cf878675489075e423&fsize=543979&rtoken=VOCi0a2e1JPd&force_default=yes&ycrid=na-984c9e19343df24c774d4c6c23ce5714-downloader21f&ts=5aa8ffd673d00&s=b53964de2c282907f16d01561ba02a77cfdafbd26d7fed840a0558616b88e5b6&pb=U2FsdGVkX18-49nMfWEDbvxJMDH6C6FusF503AnLCykJ4ekfFWFXxfDJQSan39rT_yqclITlEnK_znk70dNKgfVvBBeA9LLuxUghx4-v3Zk)

At system are being built routes and calculated data

- time in moving,
- time a moving on segment,
- distance between segments
- route distance

Geo-points are added through [API](https://github.com/Steamvis/laravel-crm/blob/master/src/app/Http/Controllers/Api/MapController.php) ([docs](https://github.com/Steamvis/laravel-crm/blob/master/docs/api.md)). 

You can create routes, adds points, end routes

![](https://s96vla.storage.yandex.net/rdisk/62274aa04dad08999042346afe5a92192c1c3660fd92a9c1df8bf98dcaed9200/5f10638c/uKJUW3GHiIUFTrrK9X6UGIpynD_e1bUhsqIxRtQn3oxjzLUbEmFC7vQFIjrc6P8OywTXxrs8YglV9obyZX9PsQ==?uid=378621291&filename=first.gif&disposition=inline&hash=&limit=0&content_type=image%2Fgif&tknv=v2&owner_uid=378621291&etag=c37c4c2bf7e5e7c3fec3398905a89da5&media_type=image&hid=80c7f6680da6ce87bad735ae3e1d6c33&fsize=12796996&rtoken=PY5DQXdTwSY0&force_default=yes&ycrid=na-9a3db81d1f6c619596ada4c0ad3752d1-downloader14e&ts=5aa8fd2f6fb00&s=26648e04e1d38cc4661f5db627f5b39d7fff8e43c68bbb8f5097ae806e6f2c19&pb=U2FsdGVkX1-9dE-3zKBN58ZxmzPA9PrPxdWeiiq_HsBbJKJGqHF4vmRJJA8OZRYUTqeAxRsDKq0OZYp8n6fH1m_ejL3dm8TY6i7o9OPgMk4)

![](https://s237myt.storage.yandex.net/rdisk/84e54fe6ae6c39ae7e6403d5447b943061c2add88fea13c43291b15fe4e22fa6/5f1063ad/uKJUW3GHiIUFTrrK9X6UGIkFTRig84nl6k_6GKsk_S-6fpF_koFxnNUVjhBkQJsM_C_KHIHRJyzZLLx3qTRSzQ==?uid=378621291&filename=second.gif&disposition=inline&hash=&limit=0&content_type=image%2Fgif&tknv=v2&owner_uid=378621291&fsize=14210745&hid=3a9c981b17887ad886de4f3ba82286a4&media_type=image&etag=d89666ff4003c6e77b874e6ac61d7dbd&rtoken=F29UduPUqiiU&force_default=yes&ycrid=na-32591fe01156c966156edf99af969d60-downloader14e&ts=5aa8fd4df4300&s=4ec69777022af3fe880c681b7ef1bcee0c4b345e16bc476006c5fccc289ffc6e&pb=U2FsdGVkX18MZ_UBAjylk-hE3npnxg8YClPoS_PXId8eUMdWM2JYiYRFltVzIPDpbgpDxb9j7NCfxvUqYbRKfeK_iJSbmiW8zMM0pSQYPOY)

![](https://s418man.storage.yandex.net/rdisk/cd10097c221dd8635651ca082799f9c47562f9aeda5e32c29e13634467276455/5f1063c9/uKJUW3GHiIUFTrrK9X6UGNyk5Or-C02tG1CW_jT76-ie2hjo2BcIWnhfdUkAA-F7XQqcexN_oX1lndibpJ79kw==?uid=378621291&filename=last.gif&disposition=inline&hash=&limit=0&content_type=image%2Fgif&tknv=v2&owner_uid=378621291&fsize=16502965&hid=35f241a68130391d77585cc46071284a&etag=ca3fa9136c5f4c9e5d9587192c6fe099&media_type=image&rtoken=Bl4eTTTLHvaj&force_default=yes&ycrid=na-48e36e0b952a1317f20b5f1aa2ab1c23-downloader14e&ts=5aa8fd68a8200&s=7bc47c80535db1433d0c41ada197e244d9133af6e50eb75ab508dc877e1d7869&pb=U2FsdGVkX18ZTbd278v80IBxTECxdPEC06pHOQWFlb6J9zaGE7dPSfUXYp1MTXwGypYeyR9pHQbk4ggm-tcAyVoE0t-HRK3O9elLd8QqgAc)

In the panel, you can create/edit transport, upload photo, watch connected/disconnected transport

![](https://s475sas.storage.yandex.net/rdisk/c40db54275288f9bc575803913f6812e0c3093ca6ce7797ccd22f5064f318f5c/5f1084f0/uKJUW3GHiIUFTrrK9X6UGH7xvLdbfSdkioEiPPHph0ASplz9oOhe9hU-9pVT65yQUlGu4NlQZqYnZYOkMdVRXw==?uid=378621291&filename=cars-dashboard.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&fsize=574535&media_type=image&hid=2fb94bfc4aacbc4ec9d1584c50f50230&etag=ee0c5063c67f126d3c504583e72544bc&rtoken=djlI5PIHPgFI&force_default=yes&ycrid=na-3302d302e412e10a4e7365dd699aac1f-downloader3f&ts=5aa91d067d9c0&s=f583d3ebd239cbd6549ed5b01d92ccd17706b2079b46c787cef8f824e1089df3&pb=U2FsdGVkX19uJby2eBahNWPhP6FKLWgaZAcajHrgs-hn1hID25aYANojZWT4Ski3XNEEW17u3hmXrNTRtsFsUrlmGSLTTbC6ObEETS3DVEM)

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

![](https://s578sas.storage.yandex.net/rdisk/eceb8384f4cfd15d99959dc995e23c2baa2ec4d29d715dcd056d21856f20900c/5f10646e/uKJUW3GHiIUFTrrK9X6UGMFc4ls8Pxb-urrrIsqFtBsQufbTLg8SXcIIGOCU863Nz9Jb8zryiRpmwN92zrxepA==?uid=378621291&filename=mailtrap-api.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&media_type=image&fsize=38013&hid=1008a7e1962990cf9a555eafbc51163b&etag=cc0aaa2d35dc6aef8363f2bd7aa5a6a9&rtoken=BtmKyowQULrj&force_default=yes&ycrid=na-0c1d77d9550d51b328a11642f34b1a04-downloader14e&ts=5aa8fe0603540&s=8c499bd026dc37848eb66c5b2af1e467efa9967653f82c8763fc7ede1ea6928c&pb=U2FsdGVkX1-CmZknfEdC84g82302Bz8cm60Mys-m_P-OPMqTYOS7yKb8XUcDN53LrejPLnLfnXBNwerwPh0uIwFzepRCO_tbu6Id76Gavl4)

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
