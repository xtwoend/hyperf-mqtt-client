<?php

namespace Xtwoend\HyperfMqttClient\Pool;

use Hyperf\Pool\Pool;
use Hyperf\Utils\Arr;
use Hyperf\Pool\Frequency;
use Xtwoend\HyperfMqttClient\MQTTConnection;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\ConnectionInterface;

class MQTTPool extends Pool
{
    protected array $config;

    public function __construct(ContainerInterface $container, protected string $name)
    {
        $config = $container->get(ConfigInterface::class);
        $key = sprintf('mqtt.%s', $this->name);
        if (! $config->has($key)) {
            throw new \InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        $this->config = $config->get($key);
        $options = Arr::get($this->config, 'pool', []);

        $this->frequency = make(Frequency::class);

        parent::__construct($container, $options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function createConnection(): ConnectionInterface
    {
        return new MQTTConnection($this->container, $this, $this->config);
    }
}