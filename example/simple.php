<?php

error_reporting( E_ALL );
ini_set( 'display_errors', 'On' );

include_once '../vendor/autoload.php';

use Dez\Config\Adapter\Json as JsonAdapter;
use Dez\Config\Adapter\Ini as IniAdapter;
use Dez\Config\Adapter\NativeArray as ArrayAdapter;

$config = new JsonAdapter( './config/config.json' );
$config->merge( new IniAdapter( './config/config.ini' ) );
$config->merge( new ArrayAdapter( './config/config.php' ) );

Dez\ORM\Bootstrap::init( $config, 'dev' );

$connection     = Dez\ORM\Bootstrap::connect();

$queries        = [];

Dez\ORM\Common\Event::instance()->attach( 'query', function( $query ) use ( & $queries ) {
    $queries[]  = $query;
} );

include_once './models/Emails.php';
include_once './models/Queue.php';
include_once './models/Replacements.php';
include_once './models/Subjects.php';
include_once './models/Temapltes.php';

foreach( Queue::all() as $queue ) {

    $emailTo        = $queue->emailTo();
    $emailFrom      = $queue->emailFrom();
    $subject        = $queue->subject();
    $template       = $queue->template();
    $replacements   = $queue->replacements();

    var_dump(
        $emailTo->getName(),
        $emailFrom->getName(),
        $subject->getSubject(),
        $template->getCode(),
        $replacements->toArray(),
        '----------------------'
    );

}