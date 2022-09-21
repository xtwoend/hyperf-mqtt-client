<?php

namespace Xtwoend\HyperfMqttClient;


class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                // 
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of redis client.',
                    'source' => __DIR__ . '/../publish/mqtt.php',
                    'destination' => BASE_PATH . '/config/autoload/mqtt.php',
                ],
            ],
        ];
    }
}
