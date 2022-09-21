<?php


return [
    'default' => [
        'server' => env('MQTT_HOST', 'broker.emqx.io'),
        'port' => env('MQTT_PORT', 1883),
        'username' => env('MQTT_USERNAME', null),
        'password' => env('MQTT_PASSWORD', null),
    ]
];