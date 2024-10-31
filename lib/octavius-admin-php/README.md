# Octavius Rocks PHP Admin

PHP Library for octavius administration.


## Get client configuration

How to get a client configuration object as admin.

```php
$configs = ConfigurationsEndpoint(
	"http://octavius.local:8081/config/%api_key%",
	 "%api_key%",
	 Request::builder()->setAdminSecret("admin_secret")
);

$serverConfiguration = $configs->getServerConfiguration("client-api-key");
```

How to get my own configuration object as a client.

```php
$config = ConfigurationsEndpoint::builder(
	"https://service.octavius.rocks/config/%api_key%",
	"%api_key%",
	Request::builder()->setClientSecret("my_secret")
);

$myServerConfiguration = $config->getServerConfiguration("my-api-key");
```

If you want to have a working client object use the quicker way:

```php
$client = $config->getClient($api_key);
$properties = $client->getProps();
```

## Connection to client server

Change client props. 

```php
$connection = new ServerConnection(
	$serverConfiguration,
	Request::builder()->setClientSecret('my_secret')
);
$response = $connection->setClientProps(
	"my-api-key",
	ClientProps::builder->setTitle("my brand new title for this client")
);
```

**Note:** for some props like *events_budget* you need a Request object with admin privileges.

You can build a ClientServerConfiguration object manually like:
```php
$serverConfiguration = new ServerConfiguration(
	"https://service.octavius.rocks,
	"/v562/"
);
```

## Query

With a valid client server connection you can use our OQL to query for data.

```php
$response = $connection->query("my_api_key", Argmuemnts::builder()->addField(Field::HITS);  
```

## Files

You can get the URL Path for JavaScripts from the JavaScript object.

```php
$minified = false;
$js = new JavaScript($serverConfiguration);
$url = $js->core($minified);
```

## Client secret

Some client configuration changes can be submitted by the client itself. For this you need a client secret that can be generated with a admin privilege connection.

```php
$secret = $connecton->generateClientSecret($api_key);
```

## Changelog

- 1.0.1
    * Refactored api with first learnings
-  1.0 
	* First Release
	