# swoole_spider

```shell script
php spider // Just do it !!
```

## Cache
```php
use Swoole\Table;
use App\Table\Cache;

$table = new Table(1<<20); // capacity size
$table->column('id', Table::TYPE_INT);
$table->create();

$cache = new Cache($table); // manage the table
```

## Server

PoolServer
```php
use App\Server\Pool\PoolServer;
$server = new PoolServer('0.0.0.0', 8080, $engine); 
$server->start();
```

SwooleServer
```php
use App\Server\Swoole\SwooleServer;
$server = new SwooleServer('0.0.0.0', 8080, $engine);
$server->start();
```

### Protocol
```
    0                   1                   2                   3
    0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1
   +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
   |                        Operation Type                         |                     
   +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
   |                          Data Length                          |
   +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
   |                         Data Payload                          |
   +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+

```