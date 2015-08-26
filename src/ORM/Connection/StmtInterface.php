<?php

    namespace Dez\ORM\Connection;

    interface StmtInterface {

        public function bindParam( $param = null, & $value = null, $type = null, $maxLength = null, $driverData = null );

        public function bindParams( array $params = array() );

        public function execute( $params = array() );

        public function fetch( $dataType = DBO::FETCH_ASSOC );

        public function loadArray();

        public function loadNum();

        public function loadObject();

        public function loadIntoObject( $target );

    }
