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

## How to create messages

A message consists of two parts, a header and a data.

The following example shows the process of creating messages by creating an attachment request type message.

First, we create the header part.
```php
$header = new \App\Gds\Message\MessageHeader("user", "0292cbc8-df50-4e88-8be9-db392db07dbc", time(), time(), App\Gds\Message\FragmentationInfo::noFragmentation(), 4);
```

After that, we create the data part.
```php
$data = new App\Gds\Message\DataTypes\DataType4("SELECT * FROM \"events-@attachment\" WHERE id='ATID202001010000000000' and ownerid='EVNT202001010000000000' FOR UPDATE WAIT 86400");
```

Once we have a header and a data, we can create the message object.
```php
$message = new \App\Gds\Message\Message($header, $data);
```

## How to send and receive messages

Messages can be sent to the GDS via WebSocket protocol. The SDK contains a WebSocket client, so you can use this to send and receive messages.
You can also find a GDS Server Simulator written in Java here. With this simulator you can test your client code without a real GDS instance.

A message can be sent as follows.

First, you need to specify your connection details.

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

You need to create a gateway object as well. A Gateway object acts as a websocket gateway between application and a remote GDS instance.

```php
$gateway =  new \App\Gds\Gateway($connection, array('timeout' => 10), $logger);
```

Finally, you need to create your own Endpoint implementation. To do this, you need to implement the App\Gds\Endpoint class. 
A basic implementation (CustomEndpoint) can be found under the App\Gds namespace.

First, we create an event message. With this message, and with the previously createad gateway, we can specify our endpoint.
It is not necessary to explicit create and send a connection message before any other message because it is done in the background based on the connection info.

So, create the event message first.

```php
$eventMessageHeader = new \App\Gds\Message\MessageHeader("user", "0dc35f9d-ad70-46aa-8983-e57880b53c8b", time(), time(), App\Gds\Message\FragmentationInfo::noFragmentation(), 2);
$operationsStringBlock = "INSERT INTO events (id, some_field, images) VALUES('EVNT202001010000000000', 'some_field', array('ATID202001010000000000'));INSERT INTO \"events-@attachment\" (id, meta, data) VALUES('ATID202001010000000000', 'some_meta', 0x62696e6172795f6964315f6578616d706c65)";
$binaryContentsMapping = array("62696e6172795f69645f6578616d706c65" => pack("C*", 23, 17, 208));
$eventMessageData = new App\Gds\Message\DataTypes\DataType2($operationsStringBlock, $binaryContentsMapping, null);
$eventMessage = new \App\Gds\Message\Message($eventMessageHeader, $eventMessageData);
```

Now, we can create the endpoint and send the message.
```php
$endpoint = new App\Gds\CustomEndpoint($gateway, $eventMessage);
$endpoint->start();
$response = $endpoint->getResponse();
```