<?php

    namespace Dez\ORM\Common;

    class DateTime extends \DateTime {

        public function mySQL() {
            return $this->format( 'Y-m-d H:i:s' );
        }

    }