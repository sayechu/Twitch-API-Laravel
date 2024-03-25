<?php

require_once __DIR__ . '/TwitchApi.php';

$client_id = '970almy6xw98ruyojcwqpop0p0o5a2';
$client_secret = 'yl0nqzjjnadd8wl7zilpr9pzuh979j';

$twitchApi = new App\Services\TwitchApi($client_id, $client_secret);
$twitchApi->getStreams();
