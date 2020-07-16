# MAP API 0.1

To create a route, you must send a POST request to the address

```HTTp
http://example.test/api/v1/gps/LATITUDE/LONGITUDE/API_CODE/START_END
```

where:

latitude & longitude - coordinates

API_CODE - generated when creating a car, where the first 10 letters are the code, and the next characters are the car ID.

![](https://s87vla.storage.yandex.net/rdisk/fa62550253983e3823f32ee4999e0b066daa90627437688a57eaa766744807db/5f106288/uKJUW3GHiIUFTrrK9X6UGGkOibGX2eYF1jkbIi4mjjKuElx_I4I1fZsiAncNRW29U6qjh_qyF3J7qwNZsbBxyQ==?uid=378621291&filename=api-code.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&media_type=image&fsize=70387&hid=48630c8e143a6198170d28fe06b42739&etag=9c28074472e05fdd45f6b639669aaabb&rtoken=1ekNrstOtUtY&force_default=yes&ycrid=na-2f1f399906140bf4a4bbc100f8ac1917-downloader14e&ts=5aa8fc3686fc0&s=f181eb2aaae82209e5dac9bb2d826291022253d74a8a2212c3eec444ba5dd9dd&pb=U2FsdGVkX19Kcu4iaC4vrL3JHuyK_8i8hQsO9eEN_CYWDx5iT2QK2J4lRyb5eEAjnFBh2Oo41kmcMBkpBi-Z_FWsO8bkFIgPG-rMWqzyQpQ)

START -  boolean value 1 or 0. if you want create route write 1

END - Boolean value 1 or 0. If you want to end the route, write 1

------

## example with postman

**create route** 

1. copy api-code

   ![](https://s118vla.storage.yandex.net/rdisk/b8a922aa5cec2de2d332b9df9dd0d4b97522b3ce5325b916b5ffdc22ad82b1d4/5f1062ac/uKJUW3GHiIUFTrrK9X6UGJAOp7-T82qsuSpSBdR99trBgfT_gBi6E0SJgbWUVyAme4iZ7e8AmQOb9SW3Hr7xSw==?uid=378621291&filename=copy-api-code.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&etag=ea6ec1d195b9a46fd058c7f801f6fe10&fsize=61345&media_type=image&hid=4bb5e472fab45f4d9b601c94203dd1f2&rtoken=EVB427nl2TXA&force_default=yes&ycrid=na-5005226879a00b63217e7ac72a622f57-downloader14e&ts=5aa8fc59d0300&s=de1d1080a0f1180200be0bbc5acc1000179782b9e50db599092b44337d5f453e&pb=U2FsdGVkX1_reBoxXURyezRzcpWHQdMqbM8L8c4UTfkgeQFV3JjXM5SF9qLtR7kijCer7lINojfBHQXsXLZo_YdlWGQkoq6JuYf-3IYnuqE)

2. send POST request on server

   ```http
   http://localhost/api/v1/gps/78/100/bcff84a64f_2/1_0
   ```


![image-20200714125300611](https://s195vla.storage.yandex.net/rdisk/763a01811a73707db22aa34b259e6123700d8686fa2717ae236b7964cc977e7f/5f1062c3/uKJUW3GHiIUFTrrK9X6UGHTSyNLXPt78hmDxsKcOfqzNUTpkJQcRMoD3z58etw4v28P2DesZNDNmOPnYFJvmvw==?uid=378621291&filename=postman.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&hid=d3b3775e3690b782fc8c06257eacf5f5&fsize=32574&media_type=image&etag=4735476115250595e81059186d43c1b9&rtoken=TfYiVjYOkEb2&force_default=yes&ycrid=na-3d50ffc7554c4e349da8daebe8d77079-downloader14e&ts=5aa8fc6fbf6c0&s=211765708df799b6e21bfadd751010d704ecdfdb0ef7af34e1c996e3e6c06211&pb=U2FsdGVkX1_GDjtxk5A1mJwTFkE4Tgas2SYhlN1KhqFlC8FjxN8-cwmSvkhuPywE3-f9fIIRbTEbXq3keFQBkcXIvwNdPnOhLXUjoFWr8vo)

![](https://s264vla.storage.yandex.net/rdisk/45e62387fd0371a8273dbb7d95e0650e8b334a4a8ab3d3d4d4ce6f2148f26e89/5f10633f/uKJUW3GHiIUFTrrK9X6UGDcg1ZrCnCbfVfQ5MwNT1bH6w-X9sWqrcr9sHvlhKGfMITpl1ISkaUx3MugPzQ23EA==?uid=378621291&filename=image-20200714125300611.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&hid=5a662a3549eb450bf5eebf8d921990eb&media_type=image&etag=b6f012cd11386a6b6d62686433bf8d5a&fsize=42253&rtoken=LRq5dGsG9TAH&force_default=yes&ycrid=na-c102ab08b5968a95ae5d9c5ca41e2c10-downloader14e&ts=5aa8fce600dc0&s=a1b3fc357cb85c680a71b4700319b1f3c05ea47524f89d91688e89e235f40533&pb=U2FsdGVkX18bmvOwpJFdZJrYHKsx2VNRZzIKivFPpLIidm0Eik1DL-0dujmZagtSThNhycI4w9it7MaT6Lsl68lyWaOTjA1anPgyHC_7i4s)

**create middle point**

send POST request on server

```http
http://localhost/api/v1/gps/79/99/bcff84a64f_2/0_0
```

![image-20200714125610604](https://s143vla.storage.yandex.net/rdisk/dc012b6036fd0df5d6fa4c7d83eaf148936a96c3c5fb11f64a87ad4f55269519/5f1062e1/uKJUW3GHiIUFTrrK9X6UGKuew9RbG5-y5J6Us37qmkK06Vz7FKiAo-rLeMPWgNEC-Ne1ItfT6IrsKhOvMOeFcg==?uid=378621291&filename=image-20200714125610604.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&fsize=99607&hid=89b85fd7c2968273d38a6c4fcf120137&etag=f6186cd940f53c4d58d3fbc2c0b0ad7c&media_type=image&rtoken=MWj6DxpqYsw4&force_default=yes&ycrid=na-7c5a2b6b86db2f4ec0729ea43a580b8e-downloader14e&ts=5aa8fc8c5ba40&s=ab86d742cdc74ac33c800b74656d0ad1983ec1391ed78c8fe874ebffe2b0b4f1&pb=U2FsdGVkX18IP3mfyEojw1cJWA0cGOhgntbHWHLJSwIA4L17ROF92xDJ4blSCMCm6fQ9VAUarz-nVmZkHGkwhv5xktvsU_y0E6pihjEqPlA)

**end route and create new**

send POST request on server to end route

```http
http://localhost/api/v1/gps/78/99.55/bcff84a64f_2/0_1
```

send POST request on server to create route

```http
http://localhost/api/v1/gps/58/49.55/bcff84a64f_2/1_0
```

![image-20200714125900985](https://s424man.storage.yandex.net/rdisk/2162be1ed25efe413f81c6a093df1e5fef17e3466d1147d1bddd970c37b4b898/5f10635a/uKJUW3GHiIUFTrrK9X6UGIjQwzHS9X2NaksK_UlriWIktomDJMkiGvf_REgnP49jlnzr0QN4JS9STDl70aMiCA==?uid=378621291&filename=image-20200714125900985.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&hid=2c1f946adbb8be255a4b114bc7d78246&media_type=image&fsize=108438&etag=992b701ce8e0cc6cfe9289fcc4e3a992&rtoken=7YUS82QNq5NM&force_default=yes&ycrid=na-929eae4bcae286788212c71bfb014ea7-downloader14e&ts=5aa8fcffc0a80&s=0acc63f14149e22c62d28a35837421cf50ba1c838cbd42abe8eee3c64a4c6719&pb=U2FsdGVkX19KLYVKQZnvO7AlzZ_QZFLDZSxdvnubpotFmAWazyAJFrT4fxnqRa77uBM30SEb7-mPM6iBiUV5R84WDnlO0fD97YQSZ2oqCP4)

