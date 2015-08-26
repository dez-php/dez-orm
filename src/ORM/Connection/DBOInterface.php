<?php

    namespace Dez\ORM\Connection;

    interface DBOInterface {

        public function prepareQuery( $query = null, array $params = array() );

        public function execute( $query = null );

        public function affectedRows();

        public function transactionStart();

        public function commit();

        public function rollback();

    }
