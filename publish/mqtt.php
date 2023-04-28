<?php

return [
    'default' => 'default',
    'interval_save' => 60,
    'servers' => [
        'default' => [
            'host' => env('MQTT_HOST', '127.0.0.1'),
            'port' => (int) env('MQTT_PORT', 1883),
            'username' => env('MQTT_USERNAME'),
            'password' => env('MQTT_PASSWORD'),
        ],
    ]
];