# telegramBotApi

## simple telegram bot API with multiple bot support for php

install with composer

```
composer require miladnazari/telegram-bot-api
```


## Usage

### create new object
```
$api = new \miladnazari\telegramBotApi\api('YOUR-BOT-TOKEN');


//create with more options

$api = new \miladnazari\telegramBotApi\api('YOUR-BOT-TOKEN'[, bool $getResult = true [, int $connectionTimeOut = 10 [, int $responseTimeOut = 50 ]]]);
```

### get me method

```
$res = $api->action('getMe')->shoot();

// return {"ok":true,"result":{"id":598765309,"is_bot":true,"first_name":"G11\ufe0f\u20e3","username":"mnrG1bot"}} in std class
```

### sendMessage method

```
$res = $api->action('sendMessage')
     ->param([
         'chat_id' => 'ID OR USERNAME',
         'text' => 'FIRTS TEXT TO SEND',
     ])
     ->shoot();
```
