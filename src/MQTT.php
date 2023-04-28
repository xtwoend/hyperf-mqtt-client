<?php

namespace Xtwoend\HyperfMqttClient;

use Xtwoend\HyperfMqttClient\MQTTConnection;
use Hyperf\Context\Context;
use Xtwoend\HyperfMqttClient\Pool\PoolFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Hyperf\Pool\Exception\ConnectionException;

class MQTT
{
    protected string $poolName = 'default';
    
    public function __call($name, $arguments)
    {
        // Get a connection from coroutine context or connection pool.
        $hasContextConnection = Context::has($this->getContextKey());
        $connection = $this->getConnection($hasContextConnection);

        try {
            $connection = $connection->getConnection();
            // Execute the command with the arguments.
            $result = $connection->{$name}(...$arguments);
        } finally {
            // Release connection.
            if (! $hasContextConnection) {
                $connection->release();
            }
        }

        return $result;
    }

    public static function __callStatic($name, $arguments)
    {
        $mqtt = ApplicationContext::getContainer()->get(MQTT::class);
        $hasContextConnection = Context::has($mqtt->getContextKey());

        if ($name === 'connection') {
            return $mqtt->getConnection($name);
        }
        
        return $mqtt->getConnection($hasContextConnection)->{$name}(...$arguments);
    }

    /**
     * Get a connection from coroutine context, or from mqtt connection pool.
     * @param mixed $hasContextConnection
     */
    private function getConnection($hasContextConnection): MQTTConnection
    {
        $mqtt = ApplicationContext::getContainer()->get(MQTT::class);
        
        $connection = null;
        if ($hasContextConnection) {
            $connection = Context::get($mqtt->getContextKey());
        }
        if (! $connection instanceof MQTTConnection) {

            $pool = make(PoolFactory::class)->getPool($mqtt->poolName);
            $connection = $pool->get();
        }
        if (! $connection instanceof MQTTConnection) {
            throw new ConnectionException('The connection is not a valid MQTTConnection.');
        }
        return $connection;
    }

    /**
     * The key to identify the connection object in coroutine context.
     */
    private function getContextKey(): string
    {
        return sprintf('mqtt.connection.%s', $this->poolName);
    }
}