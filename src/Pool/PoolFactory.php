<?php

namespace Xtwoend\HyperfMqttClient\Pool;

use Hyperf\Di\Container;
use Psr\Container\ContainerInterface;

class PoolFactory
{
    /**
     * @var array<string, MQTTPool>
     */
    protected array $pools = [];

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function getPool(string $name): MQTTPool
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        if ($this->container instanceof Container) {
            $pool = $this->container->make(MQTTPool::class, ['name' => $name]);
        } else {
            $pool = new MQTTPool($this->container, $name);
        }

        return $this->pools[$name] = $pool;
    }
}
