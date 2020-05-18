## Installation

The library can be installed as a composer package.

Include this repository in your composer.json:

```JSON
"repositories": [ {
        "type": "vcs",
        "url": "https://github.com/arh-eu/gds-php-messages.git"
}]
```

```JSON
"require": {
	"arh/gds-messages": "@dev"
}
```

## Examples

### Create the Connection

First, you need to specify your connection details:

```php
$connectionInfo = new \App\Gds\ConnectionInfo(
        "ws://user@127.0.0.1:8080/gate",
        \App\Gds\Message\FragmentationInfo::noFragmentation(),
        true,
        0x01000000,
        false,
        null,
        null);
```

Also you need to create a Logger object which implement the \Psr\Log\AbstractLogger class. A basic implementation (CustomLogger) can be found under the App\Gds namespace.

```php
$logger = new \App\Gds\CustomLogger();
```

Last, youd need to create the EventLoop object:

```php
$eventLoop = new React\EventLoop\StreamSelectLoop();
```

After that, you can specify you connection as follows:

```php
$connection = new \App\Gds\Connection($connectionInfo, $eventLoop, $logger);
```

### Create the Gateway

A Gateway object acts as a websocket gateway between application and a remote GDS instance.

```php
$gateway =  new \App\Gds\Gateway($connection, array('timeout' => 10), $logger);
```

### Create the Endpoint

Finally, you need to create your own Endpoint implementation. To do this, you need to implement the App\Gds\Endpoint class. A basic implementation (CustomEndpoint) can be found under the App\Gds namespace.

Suppose we would like to send an attachment request. To do this, we need to create the Message object. A message consists of two parts, a header and a data.

The following example shows how to create the header part:

```php
$header = new \App\Gds\Message\MessageHeader("user", "0dc35f9d-ad70-46aa-8983-e57880b53c8b", time(), time(), App\Gds\Message\FragmentationInfo::noFragmentation(), 4);
```

The data part is made in the same way:
```php
$data = new App\Gds\Message\DataTypes\DataType4("SELECT meta, data, \"@to_valid\" FROM \"events-@attachment\" WHERE id = ’ATID202000000000000000’ and ownerid = ’EVNT202000000000000000’ FOR UPDATE WAIT 86400");
```

Once you have the header and the data part, the message can be created:
```php
$message = new \App\Gds\Message\Message($header, $data);
```

Now, you can create the Endpoint:
```php
$endpoint = new App\Gds\CustomEndpoint($gateway, $message);
```

### Send the Message

To send the created message and wait for the response:

```php
$endpoint->start();
$response = $endpoint->getResponse();
```
