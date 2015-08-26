<?php

    namespace Dez\ORM\Common;

    use Dez\ORM\Collection\Collection;

    class DataFormat {

        static public function getArrayValue( Collection $collection ) {
            $rows = [];
            $collection->each( function( $i, $row ) use ( & $rows ) {
                $rows[$row->id()]   = $row->current();
            } );
            return $rows;
        }

        static public function getArrayGroupedByColumn( Collection $collection, $columnName = null ) {
            $rows = [];
            $collection->each( function( $i, $row ) use ( & $rows, $columnName ) {
                $rows[$row->get( $columnName )][]    = $row;
            } );
            return $rows;
        }

    }