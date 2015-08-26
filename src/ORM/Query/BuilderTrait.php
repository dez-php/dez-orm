<?php

    namespace Dez\ORM\Query;

    trait BuilderTrait {

        protected function _prepareColumn( $column = null, $separate = false ) {

            $alias  = null;
            if( strpos( $column, "\x20" ) !== false ) {
                list( $column, $alias ) = explode( "\x20", $column );
                $alias  = $this->_escapeName( trim( $alias ) );
            }

            $tableName  = $this->tableName;
            if( strpos( $column, '.' ) !== false ) {
                list( $tableName, $column ) = explode( '.', $column );
                $tableName  = $this->_escapeName( trim( $tableName ) );
            }

            $column = $column != '*' ? $this->_escapeName( trim( $column ) ) : $column;

            return $separate == true
                ? array( $tableName, $column, $alias )
                : $tableName .'.'. $column . ( ! $alias ? null : "\x20". $alias );
        }

        protected function _escapeName( $string = null  ) {
            return ! empty( $string ) ? '`'. $string .'`' : null;
        }

        protected function _escapeData( $string = null  ) {
            return ! empty( $string ) ? '\''. $string .'\'' : null;
        }

    }