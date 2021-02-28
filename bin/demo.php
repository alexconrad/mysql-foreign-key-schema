<?php

use EasyMysql\Enum\MysqlDriver;
use MysqlForeignKeySchema\MysqlForeignKey;

require_once '../vendor/autoload.php';

$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
    'easyMysqlDriver' => MysqlDriver::MYSQLI(),
    'easyMysqlHost' => '192.168.24.24',
    'easyMysqlPort' => 3306,
    'easyMysqlUser' => 'devel',
    'easyMysqlPass' => 'withc--',
    'easyMysqlName' => null,
]);
\EasyMysql\PhpDi::addDefinitions($builder);
$container = $builder->build();

$dataProvider = $container->get(MysqlForeignKey::class);

$dataProvider->go();

