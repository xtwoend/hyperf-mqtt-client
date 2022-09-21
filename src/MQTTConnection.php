<?php

namespace Xtwoend\HyperfMqttClient;

use Hyperf\Utils\Str;
use PhpMqtt\Client\MqttClient;
use Hyperf\Contract\PoolInterface;
use PhpMqtt\Client\ConnectionSettings;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Exception\ConnectionException;

class MQTTConnection extends BaseConnection implements ConnectionInterface
{
    protected $config = [];

    public function __construct(ContainerInterface $container, PoolInterface $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->config = array_replace_recursive($this->config, $config);

        $this->reconnect();
    }

    public function getActiveConnection()
    {
        if ($this->check()) {
            return $this;
        }

        if (! $this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this;
    }

    /**
     * @throws ConnectionException
     */
    public function reconnect(): bool
    {
        $server = $this->config['server'];
        $port = $this->config['port'];
        $username = $this->config['username'];
        $password = $this->config['password'];

        $clientId = 'mqtt_'.Str::random(100);

        $mqtt = new MqttClient($server, $port, $clientId);
        $setting = (new ConnectionSettings)
            ->setUsername($username ?: null)
            ->setPassword($password ?: null);
        
        $mqtt->connect($setting, true);
        
        $this->connection = $mqtt;
        $this->lastUseTime = microtime(true);

        return true;
    }

    public function close(): bool
    {
        if(isset($this->connection)) {
            $this->connection->disconnect();
        }

        unset($this->connection);

        return true;
    }

    public function release(): void
    {
        parent::release();
    }

    public function instance(): MqttClient
    {
        return $this->connection;
    }
    
    public function publish(string $topic, $message): void
    {
        if( ! $this->connection instanceof MqttClient) {
            if (! $this->reconnect()) {
                throw new ConnectionException('Connection reconnect failed.');
            }
        }

        $mqtt = $this->connection;
        $mqtt->publish($topic, $message, 0);
    }
}