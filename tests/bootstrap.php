<?php

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

$dbPath = dirname(__DIR__).'/var/test.db';
if (file_exists($dbPath)) {
    unlink($dbPath);
}

$kernel = new App\Kernel('test', true);
$kernel->boot();
$entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');
$metadata = $entityManager->getMetadataFactory()->getAllMetadata();
if ($metadata !== []) {
    (new SchemaTool($entityManager))->createSchema($metadata);
}
$kernel->shutdown();

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
