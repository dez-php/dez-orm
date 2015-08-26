<?php

error_reporting( E_ALL );
ini_set( 'display_errors', 'On' );

include_once '../vendor/autoload.php';

Dez\ORM\Bootstrap::init( './config/connect.ini', 'dev' );

// PDO $connection
$connection     = Dez\ORM\Bootstrap::connect();

$queries        = [];
Dez\ORM\Common\Event::instance()->attach( 'query', function( $query ) use ( & $queries ) {
    $queries[]  = $query;
} );


class Robots extends Dez\ORM\Model\Table {
    static protected $table   = 'robots';
}

$i = 0;

while( $i < 10 ) {
    ( new Robots )
        ->setName( "Robot #". rand( 1, 100 ) )
        ->setCreated( ( new Dez\ORM\Common\DateTime() )->mySQL() )
        ->save();
    $i++;
}

foreach ( Robots::all() as $robot ) {
    print "#{$robot->id()} - {$robot->getName()}<br />";
    $robot->setName( $robot->getName() . '_update' )->save();
}

print '<pre>'. implode( "\n\n", $queries ) .'</pre>';

die;