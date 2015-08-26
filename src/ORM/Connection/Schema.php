<?php

    namespace Dez\ORM\Connection;

    use Dez\ORM\Exception\Error as ORMException;

    class Schema implements SchemaInterface {

        private
            $databaseName   = null,
            $charset        = null,
            $tables         = array(),
            $columns        = array();

        public function __construct( $schemaFile = null ) {
            if( ! file_exists( $schemaFile ) ) {
                throw new ORMException( 'Schema file not found ( File: '. $schemaFile .' )' );
            }
            $xmlElement = simplexml_load_file( $schemaFile );
            $this->startLoadSchema( $xmlElement );
        }

        public function getTablePK( $tableName ) {
            static $pks = array();

            if( isset( $pks[$tableName] ) ) {
                return $pks[$tableName];
            }

            $pks[$tableName] = 'id';
            if( $this->tableExist( $tableName ) ) {
                foreach( $this->columns[$tableName] as $pkName => $column ) {
                    if( isset( $column['pk'] ) && $column['pk'] == 1 ) {
                        $pks[$tableName] = $pkName; break;
                    }
                }
            }

            return $pks[$tableName];
        }

        public function getColumns ( $tableName ) {
            if( $this->tableExist( $tableName ) ) {
                return $this->columns[$tableName];
            } else {
                return false;
            }
        }

        public function getTables ( $databaseName ) {
            return $this->tables;
        }

        public function tableExist ( $tableName ) {
            return in_array( $tableName, $this->tables );
        }

        public function columnExist ( $tableName, $columnName ) {
            return isset( $this->columns[$tableName][$columnName] );
        }

        private function startLoadSchema( \SimpleXMLElement $xml ) {
            if( isset( $xml->{ 'database' } ) ) {
                $databaseNode = json_decode( json_encode( $xml->database ), true );

                if( isset( $databaseNode['@attributes'] ) ) {
                    $databaseAttrs      = & $databaseNode['@attributes'];
                    $this->databaseName = isset( $databaseAttrs['name'] )       ? $databaseAttrs['name'] : 'default-db';
                    $this->charset      = isset( $databaseAttrs['charset'] )    ? $databaseAttrs['charset'] : 'utf8';
                }

                if( isset( $databaseNode['table'] ) && isset( $databaseNode['table'] ) && count( $databaseNode['table'] ) > 0 ) {
                    $this->collectTables( $databaseNode['table'] );
                } else {
                    throw new ORMException( 'Wrong schema node ( Not found node: table )' );
                }
            } else {
                throw new ORMException( 'Wrong schema node ( Not found node: database )' );
            }
        }

        private function collectTables( array $tables = array() ) {
            $tables = isset( $tables['@attributes'] ) ? array( $tables ) : $tables;
            foreach( $tables as $table ) {
                if( isset( $table['@attributes'] ) ) {
                    $this->tables[] = $table['@attributes']['name'];

                    if( isset( $table['column'] ) && isset( $table['column'] ) && ! empty( $table['column'] ) ) {
                        $this->collectColumns( $table['@attributes']['name'], $table['column'] );
                    } else {
                        throw new ORMException( 'Not found columns for table: '. $table['@attributes']['name'] );
                    }
                } else {
                    throw new ORMException( 'Unknown table name' );
                }
            }
        }

        private function collectColumns( $tableName = null, array $columns = array() ) {
            if( $tableName ) {
                $this->columns[$tableName] = array();
                $columns = isset( $columns['@attributes'] ) ? array( $columns ) : $columns;
                foreach( $columns as $column ) {
                    if( isset( $column['@attributes'] ) ) {
                        $attrs                                      = $column['@attributes'];
                        $this->columns[$tableName][$attrs['name']]  = $attrs; unset( $attrs );
                    } else {
                        throw new ORMException( 'Not set column properties' );
                    }
                }
            }
        }

    }