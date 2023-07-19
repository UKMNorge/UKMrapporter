<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Arrangement\Arrangement;

$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);

$arrangement = new Arrangement(get_option('pl_id'));


$arrRes = [
    'status' => true,
    'test' => 'YoYo!'
];

$handleCall->sendToClient($arrRes);