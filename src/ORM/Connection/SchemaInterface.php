<?php

    namespace Dez\ORM\Connection;

    interface SchemaInterface {

        public function getColumns ( $tableName );

        public function getTables ( $databaseName );

        public function tableExist ( $tableName );

        public function columnExist ( $tableName, $columnName );

    }