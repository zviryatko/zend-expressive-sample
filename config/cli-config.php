<?php
/** 
 * Configuration for doctrine cli. 
 * 
 * This file is auto detected and used by doctrine cli.
 */

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\EntityManager;

require 'vendor/autoload.php';

/** @var \Interop\Container\ContainerInterface $container */
$container = require 'config/container.php';

/** @var \Doctrine\ORM\EntityManager $em */
$em = $container->get(EntityManager::class);

return ConsoleRunner::createHelperSet($em);
