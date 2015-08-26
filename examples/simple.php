<?php

    error_reporting( E_ALL );
    ini_set( 'display_errors', 'On' );

    include_once '../vendor/autoload.php';

    Dez\ORM\Bootstrap::init( './config/connect.ini', 'dev' );

    $connection     = Dez\ORM\Bootstrap::connect();

    // PDO $connection