Thelia API client
===

What is this ?
---
This is a PHP client for [Thelia](https://github.com/thelia/thelia) API.

How to use it ?
---
First, add ```thelia/api-client``` to your composer.json

```json
{
    "require": {
        # ...
        "thelia/api-client": "~1.0"
    }
}
```

Then, create an instance of ```Thelia\Api\Client\Client``` with the following parameters:

```php
$client = new Thelia\Api\Client\Client("my api token", "my api key", "http://mysite.tld");
```

You can access to your resources by using the 'do*' methods

```php
<?php
list($status, $data) = $client->doList("products");
list($status, $data) = $client->doGet("products/1/image", 1);
list($status, $data) = $client->doPost("products", ["myData"]);
list($status, $data) = $client->doPut("products", ["myData"]);
list($status, $data) = $client->doDelete("products", 1);
```

Or you can use magic methods that are composed like that: ```methodEntity```

```php
<?php
list($status, $data) = $client->listProducts();
list($status, $data) = $client->getTaxes(42);
list($status, $data) = $client->postPse($data);
list($status, $data) = $client->putTaxRules($data);
list($status, $data) = $client->deleteAttributeAvs(42);
```

Tests
---
To run the tests, edit the file tests/server.txt and place your thelia address