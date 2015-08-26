<?php

    namespace Dez\ORM\Common;

    class Utils {

        static public function php2sql( $name = null ) {
            $parts = preg_split( '/(?=[A-Z]+)/u', $name, -1, PREG_SPLIT_NO_EMPTY );
            return join( '_', array_map( 'strtolower', $parts ) );
        }

    }