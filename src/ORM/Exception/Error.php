<?php

    namespace Dez\ORM\Exception;

    class Error extends \Exception {
        public function __construct( $message = null ) {
            $prev = null;
            if( $message !== null ) {
                $prev       = $message;
                $message    = null;
            }

            if( $prev !== null ) {
                $message = 'ORMException [' . $prev . ']';
                parent::__construct( $message );
            } else {
                parent::__construct( $message );
            }
        }
    }