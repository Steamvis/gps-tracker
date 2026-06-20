# MAP API 0.1

To create a route, you must send a POST request to the address

```HTTp
http://example.test/api/v1/gps/LATITUDE/LONGITUDE/API_CODE/START_END
```

where:

latitude & longitude - coordinates

API_CODE - generated when creating a car, where the first 10 letters are the code, and the next characters are the car ID.

![api-code](https://user-images.githubusercontent.com/20637799/87759476-0d3b1400-c817-11ea-891a-c6415dabdce4.png)

START -  boolean value 1 or 0. if you want create route write 1

END - Boolean value 1 or 0. If you want to end the route, write 1

------

## example with postman

**create route** 

1. copy api-code

   ![copy-api-code](https://user-images.githubusercontent.com/20637799/87759481-0f04d780-c817-11ea-9d24-2e14ff6fd707.png)

2. send POST request on server

   ![postman](https://user-images.githubusercontent.com/20637799/87759507-16c47c00-c817-11ea-9fe8-96cd1256fe30.png)

   ```http
   http://localhost/api/v1/gps/78/100/bcff84a64f_2/1_0
   ```


![image-20200714125300611](https://user-images.githubusercontent.com/20637799/87759483-10360480-c817-11ea-9dcb-7267c2135a5b.png)

**create middle point**

send POST request on server

```http
http://localhost/api/v1/gps/79/99/bcff84a64f_2/0_0
```

![image-20200714125610604](https://user-images.githubusercontent.com/20637799/87759485-10ce9b00-c817-11ea-893f-279620a957b4.png)

**end route and create new**

send POST request on server to end route

```http
http://localhost/api/v1/gps/78/99.55/bcff84a64f_2/0_1
```

send POST request on server to create route

```http
http://localhost/api/v1/gps/58/49.55/bcff84a64f_2/1_0
```

![image-20200714125900985](https://user-images.githubusercontent.com/20637799/87759488-11673180-c817-11ea-8f4d-8347147da5dd.png)