# MAP API 0.1

To create a route, you must send a POST request to the address

```HTTp
http://example.test/api/v1/gps/LATITUDE/LONGITUDE/API_CODE/START_END
```

where:

latitude & longitude - coordinates

API_CODE - generated when creating a car, where the first 10 letters are the code, and the next characters are the car ID.

![](https://s87vla.storage.yandex.net/rdisk/96aeed48d05b30b4ae42427e091fd07ccff04272fe3c1d8cc8c1cca6fac083fa/5f10a75c/uKJUW3GHiIUFTrrK9X6UGGkOibGX2eYF1jkbIi4mjjKuElx_I4I1fZsiAncNRW29U6qjh_qyF3J7qwNZsbBxyQ==?uid=378621291&filename=api-code.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&media_type=image&fsize=70387&hid=48630c8e143a6198170d28fe06b42739&etag=9c28074472e05fdd45f6b639669aaabb&rtoken=LM73lk8UR8yS&force_default=yes&ycrid=na-750de2a4e663ee0fdffdf68f0b874976-downloader7h&ts=5aa93ddb38f00&s=677800342be27d24ec9c59ecbcdeb48d5803ea6120cb0ced4f1fbb95ae42d8b3&pb=U2FsdGVkX18VGiKy8WvpKDOafruqTxGqXEdCjgntjvrz5dfxiEJ-2ctRXHpTFQXL0I9H5Y6VaQQznxA0mW13bjow_dhY-vEoqNpsqNHeAg0)

START -  boolean value 1 or 0. if you want create route write 1

END - Boolean value 1 or 0. If you want to end the route, write 1

------

## example with postman

**create route** 

1. copy api-code

   ![](https://s118vla.storage.yandex.net/rdisk/1cc5962039b7e2f9ad1a56ca2643bc507103d2c693426d412ea1288f8abf3b31/5f10a773/uKJUW3GHiIUFTrrK9X6UGJAOp7-T82qsuSpSBdR99trBgfT_gBi6E0SJgbWUVyAme4iZ7e8AmQOb9SW3Hr7xSw==?uid=378621291&filename=copy-api-code.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&media_type=image&fsize=61345&hid=4bb5e472fab45f4d9b601c94203dd1f2&etag=ea6ec1d195b9a46fd058c7f801f6fe10&rtoken=jUyYQxJUOPLD&force_default=yes&ycrid=na-c4d39f3c27a7a9c755b780bcb6876d06-downloader7h&ts=5aa93df1282c0&s=c1b9b7ac173bfdd87b129de567c9196307c382d23cb9722fbd961578bb1953c0&pb=U2FsdGVkX18Z-Zs-FcEZCapeDV61wrJCsmnv7R_NoUTvJI2Bu9px24TZEK7JIt-a1o-IdrcDlrgP2MOb_GUNpEc2_IidJTMkIlD6FIh9JRw)

2. send POST request on server

   ![](https://s195vla.storage.yandex.net/rdisk/8f07e6b803cf5a4b27efcb3cf854cf73273789fbfc0395353b8619777827d2d5/5f10a794/uKJUW3GHiIUFTrrK9X6UGHTSyNLXPt78hmDxsKcOfqzNUTpkJQcRMoD3z58etw4v28P2DesZNDNmOPnYFJvmvw==?uid=378621291&filename=postman.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&etag=4735476115250595e81059186d43c1b9&fsize=32574&hid=d3b3775e3690b782fc8c06257eacf5f5&media_type=image&rtoken=YO3DFOQrLyY0&force_default=yes&ycrid=na-a3f9bbbce1496f6987a701e8d145733a-downloader7h&ts=5aa93e10a0d00&s=1d41357fa489a7ee2a0fbd30350ccc69a98edaa53fd66c6847a751e87b59fa15&pb=U2FsdGVkX18wQWWKGLdDBtIS7i0P6uCRUgvNfC5fLO71IfbRJeOcn0IHeobx86ktHubgeMeBNoYkyThFxxi8YoGzPyzNiqQ2z-dpS-LzpQ0)

   ```http
   http://localhost/api/v1/gps/78/100/bcff84a64f_2/1_0
   ```




![](https://s264vla.storage.yandex.net/rdisk/8ca4f73848f5550a9866360b58dd1ee8f7bbdeb85795b3d5b2fdd45e58c9873a/5f10a7b1/uKJUW3GHiIUFTrrK9X6UGDcg1ZrCnCbfVfQ5MwNT1bH6w-X9sWqrcr9sHvlhKGfMITpl1ISkaUx3MugPzQ23EA==?uid=378621291&filename=image-20200714125300611.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&media_type=image&fsize=42253&hid=5a662a3549eb450bf5eebf8d921990eb&etag=b6f012cd11386a6b6d62686433bf8d5a&rtoken=pQnWjhxiSXFm&force_default=yes&ycrid=na-7b7ea7c7d5c9e4d1ecf2f0b21effd664-downloader7h&ts=5aa93e2b54c00&s=628d9f73999a9c34cd631fee37c16556d98c3481393e90dbb231fd99c1c3dd00&pb=U2FsdGVkX1_3Vz9OMqsk0t25jGj7lq_pwozzJG0OlSw9jb0OiAqvSkM_sAJEvfk-PuwSNY8Qdx1sKQ4f9bExcpExtUXMKHxS5TlYBmLknrQ)

**create middle point**

send POST request on server

```http
http://localhost/api/v1/gps/79/99/bcff84a64f_2/0_0
```

![image-20200714125610604](https://s307man.storage.yandex.net/rdisk/8becf7bdf65d729786e27e6d27371b56a35343e44a589d991bc167197018aa8b/5f10a7d4/uKJUW3GHiIUFTrrK9X6UGKuew9RbG5-y5J6Us37qmkK06Vz7FKiAo-rLeMPWgNEC-Ne1ItfT6IrsKhOvMOeFcg==?uid=378621291&filename=image-20200714125610604.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&etag=f6186cd940f53c4d58d3fbc2c0b0ad7c&fsize=99607&hid=89b85fd7c2968273d38a6c4fcf120137&media_type=image&rtoken=c1v9XnllhJMD&force_default=yes&ycrid=na-8cb88a0eef9a2781eb1687a78f8db6c0-downloader7h&ts=5aa93e4cb5ac0&s=43e08bc1270397809411713fa3f5066582e4b0e071dd09f4491f807b72e971c8&pb=U2FsdGVkX1_Lz7Jo1l0uujvnz5jAKMkquqfiP8RM8l_eCeN6FWHNA3SwGgj_AusS-b5M0Gsi0GQQ7yXE5Dv_db0U3BKeOKiDE4b4oUsT7yU)

**end route and create new**

send POST request on server to end route

```http
http://localhost/api/v1/gps/78/99.55/bcff84a64f_2/0_1
```

send POST request on server to create route

```http
http://localhost/api/v1/gps/58/49.55/bcff84a64f_2/1_0
```

![image-20200714125900985](https://s138vla.storage.yandex.net/rdisk/49c4a9245caceca397ad2e6e673b4295d48641ba49fd8d4d2ad89b6401040dda/5f10a7de/uKJUW3GHiIUFTrrK9X6UGIjQwzHS9X2NaksK_UlriWIktomDJMkiGvf_REgnP49jlnzr0QN4JS9STDl70aMiCA==?uid=378621291&filename=image-20200714125900985.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&tknv=v2&owner_uid=378621291&hid=2c1f946adbb8be255a4b114bc7d78246&media_type=image&fsize=108438&etag=992b701ce8e0cc6cfe9289fcc4e3a992&rtoken=IAK0wV4ddjbA&force_default=yes&ycrid=na-d222f73125ebe4763b07de871050423b-downloader7h&ts=5aa93e5733380&s=f28e76feb3e1cf2e0d8752693cb5a92fd2ccfb5581c523731dda11966aaa3292&pb=U2FsdGVkX18MIiVnLF0SmvqIjNa4Q-60AmCZHPOnVLh11qjtQ-Jt5ZKdG7KmWkUSNzQssk4ibmoSa2DvEacy3vQhmDuZ7mSgT0NgSqYapH8)

