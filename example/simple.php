<?php

error_reporting( E_ALL );
ini_set( 'display_errors', 'On' );

include_once '../vendor/autoload.php';

use Dez\Config\Adapter\Json as JsonAdapter;
use Dez\Config\Adapter\NativeArray as ArrayAdapter;

$config = new JsonAdapter( './config/config.json' );
$config->merge( new ArrayAdapter( './config/config.php' ) );

Dez\ORM\Connection::init( $config, 'dev' );

class Post extends \Dez\ORM\Model\Table {
    static protected $table = 'posts';
}

$connection     = Dez\ORM\Connection::connect();

$queries        = [];

Dez\ORM\Common\Event::instance()->attach( 'query', function( $query ) use ( & $queries ) {
    $queries[]  = $query;
} );

foreach( Post::all() as $item ) {
    var_dump(
        $item->toArray(),
        '----------------------'
    );
}

var_dump($queries);