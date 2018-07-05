# MC-Protocols


## Install
The recommended way to install this is the composer.
```json
{
  "require": {
	"pocketmine/binaryutils": "^0.1.0",
	"frago9876543210/mc-protocols": "dev-master"
  },
  "repositories": [
	{
	  "type": "vcs",
	  "url": "https://github.com/pmmp/BinaryUtils"
	},
	{
	  "type": "vcs",
	  "url": "https://github.com/Frago9876543210/MC-Protocols"
	}
  ]
}
```

## Examples

### Query
```php
<?php

declare(strict_types=1);

require_once "vendor/autoload.php";

try{
	$query = new \Frago9876543210\Query\Query("play.lbsg.net", 19132);
	$result = $query->getResult();
	print_r($result);
}catch(Exception $e){
	echo $e->getMessage();
}
```

### RCON
```php
<?php

declare(strict_types=1);

require_once "vendor/autoload.php";

try{
	$rcon = new \Frago9876543210\RCON\RCON("127.0.0.1", 19132, "password");
	$rcon->executeCommand("list");
	echo $rcon->getOutput();
}catch(Exception $e){
	echo $e->getMessage();
}
```
