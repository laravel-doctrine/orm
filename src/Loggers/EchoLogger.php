<?php

namespace LaravelDoctrine\ORM\Loggers;

use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use const E_USER_DEPRECATED;

/**
 * @deprecated
 */
class EchoLogger implements Logger
{
    /**
     * @param EntityManagerInterface $em
     * @param Configuration          $configuration
     */
    public function register(EntityManagerInterface $em, Configuration $configuration)
    {
        $configuration->setSQLLogger(new class implements SQLLogger {
            public function __construct()
            {
                @trigger_error('EchoLogger is deprecated without replacement, move the code into your project if you rely on it.', E_USER_DEPRECATED);
            }

            /**
             * {@inheritdoc}
             */
            public function startQuery($sql, ?array $params = null, ?array $types = null)
            {
                echo $sql . PHP_EOL;

                if ($params) {
                    var_dump($params);
                }

                if (! $types) {
                    return;
                }

                var_dump($types);
            }

            /**
             * {@inheritdoc}
             */
            public function stopQuery()
            {
            }
        });
    }
}
