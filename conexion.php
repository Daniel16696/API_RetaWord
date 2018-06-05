<?php 
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

// $capsule->addConnection([
//     'driver'    => 'mysql',
//     'host'      => 'localhost',
//     'database'  => 'retos',
//     'username'  => 'root',
//     'password'  => '',
//     'charset'   => 'utf8',
//     'collation' => 'utf8_unicode_ci',
//     'prefix'    => '',
// ]);

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'db738266318.db.1and1.com',
    'database'  => 'db738266318',
    'username'  => 'dbo738266318',
    'password'  => 'daniel_96',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Set the event dispatcher used by Eloquent models... (optional)
// use Illuminate\Events\Dispatcher;
// use Illuminate\Container\Container;
// $capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();
?>