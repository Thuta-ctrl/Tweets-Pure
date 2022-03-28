# Twitter Api Package

## Installation

```ruby
composer require thutayarmoe/puretweets
```

## How to use

```
<?php

include_once "vendor/autoload.php";

use ThutaYarMoe\PureTweets\Tweets;

$users = [
    [
        'id'=>'user id',
        'token'=>'token'
    ],
];
$data = new Tweets();
$tweets = $data->get_tweets($users);

echo "<pre>";print_r($tweets);"</pre>";
```
