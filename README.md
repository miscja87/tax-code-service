## 📋 About

The **Tax Code Service** is a simple JSON API service for extraction of informations and generation of italian tax codes. It's currently deployed on:

[http://www.handlesport.com/tax-code-service](http://www.handlesport.com/tax-code-service "Tax Code Service")

## 🛠️ Configuration

To deploy the service you need to have a server running PHP (local or remote). Once cloned the project you must install the required libraries using composer in project main directory:

```Bash
composer install
```

And configuring the **API HOST** endpoind in **config.php** file:

```PHP
define("API_HOST", "http://www.handlesport.com/tax-code-service");
```

## 👷 Built with

- [PHP](https://www.php.net/ "PHP"): as main coding language

- [Fligth PHP](https://flightphp.com/ "Flight"): an extensible micro-framework for PHP for building RESTful web applications

- [Swagger PHP](https://zircote.github.io/swagger-php/ "Swagger PHP"): for generation of OpenAPI documentaion

## ✔️ Testing

You can test the services:

- Accessing [Swagger](http://www.handlesport.com/tax-code-service/swagger "SWAGGER") with documentation of the provided API and test the services

- Install [Postman](https://www.postman.com/ "PostMan") and import the example [workset](https://github.com/miscja87/tax-code-service/blob/main/postman/TaxCodeService.postman_collection.json "workset")

## ✏️ To do

- 📌 Improve request validation

- 📌 Database integration

- 📌 Add caching

- 📌 Complete documentation

## 🧑🏻 Author

**Mikhail Vorontsov**

- 🌌 [Profile](https://www.linkedin.com/in/mikhailvorontsov "Mikhail Vorontsov")

- 📧 [Email](mailto:miscja@hotmail.com "Email")
