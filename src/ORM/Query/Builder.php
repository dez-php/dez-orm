<?php

    namespace Dez\ORM\Query;

    use \Dez\ORM\Exception\Error as ORMException,
        \Dez\ORM\Connection;

    class Builder {

        use BuilderTrait;

        const
            BUILD_TYPE_SELECT   = 1,
            BUILD_TYPE_UPDATE   = 2,
            BUILD_TYPE_INSERT   = 3,
            BUILD_TYPE_DELETE   = 4;

        private
            $connection         = null,

            $query              = null,
            $tableName          = null,
            $data               = array(),
            $buildType          = self::BUILD_TYPE_SELECT,

            $selectColumns      = array(),

            $insertIgnore       = false,

            $where              = array(),

            $group              = array(),
            $groupAliases       = array(),

            $order              = array(),
            $orderAliases       = array(),

            $limit              = array(),

            $joins              = array();

        private static
            $cmpTypes   = array( '=', '>', '<', '>=', '<=', '!=', '<>' );

        public function __construct( Connection\DBO $connection ) {
            $this->connection = $connection;
        }

        public function __toString() {
            return (string) $this->query();
        }

        public function __invoke( $tableName ) {
            $this->table( $tableName );
        }

        public function getConnection() {
            return $this->connection;
        }

        public function func( $functionName = null, $column = null, array $sqlFuncArgs = array() ) {
            if( ! empty( $functionName ) ) {
                $className  = __NAMESPACE__ . '\\Func\\' . ucfirst( strtolower( $functionName ) );
                if( class_exists( $className ) ) {
                    return ( new $className )->wrap( $this->tableName, $column, $sqlFuncArgs );
                } else {
                    throw new ORMException( 'Function not found ['. $className .']' );
                }
            } else {
                return new \stdClass();
            }
        }

        public function join( $type, $table, $joinTable, array $expression = [] ) {
            $this->joins[] = new Join(
                $type,
                $this->_escapeName( $table ),
                $this->_escapeName( $joinTable ),
                $expression
            );
            return $this;
        }

        public function innerJoin( $table, $joinTable, array $expression = [] ) {
            return $this->join( 'inner', $table, $joinTable, $expression );
        }

        public function leftJoin( $table, $joinTable, array $expression = [] ) {
            return $this->join( 'left', $table, $joinTable, $expression );
        }

        public function rightJoin( $table, $joinTable, array $expression = [] ) {
            return $this->join( 'right', $table, $joinTable, $expression );
        }

        public function bind( array $data = array() ) {
            if( ! empty( $data ) ) {
                foreach( $data as $key => $value ) {
                    $this->data[$key] = $value;
                }
            }
            return $this;
        }

        public function select( array $columns = array(), $merge = true ) {
            $this->buildType = self::BUILD_TYPE_SELECT;
            if( ! empty( $columns ) ) {
                $this->selectColumns = ! $merge ? $columns : array_merge( $this->selectColumns, $columns );
            }
            return $this;
        }

        public function update() {
            $this->buildType = self::BUILD_TYPE_UPDATE;
            return $this;
        }

        public function insert() {
            $this->buildType = self::BUILD_TYPE_INSERT;
            return $this;
        }

        public function ignore() {
            $this->insertIgnore = true;
            return $this;
        }

        public function delete() {
            $this->buildType = self::BUILD_TYPE_DELETE;
            return $this;
        }

        public function table( $tableName = null ) {
            $this->tableName = $this->_escapeName( $tableName );
            return $this;
        }

        public function where() {
            $expressions = func_get_args();

            if( ! empty( $expressions ) ) {
                foreach( $expressions as $expression ) {
                    $cmpType = ( isset( $expression[2] ) && in_array( $expression[2], self::$cmpTypes ) )
                        ? $expression[2]
                        : self::$cmpTypes[0];
                    if( is_array( $expression[1] ) && count( $expression[1] ) > 0 ) {
                        $this->where[]  = [ $expression[0], $expression[1] ];
                    } else {
                        $this->where[]  = [ $expression[0], $expression[1], $cmpType ];
                    }
                }
            }

            return $this;
        }

        public function whereRaw( $query = '' ) {
            $this->where[]  = $query;
            return $this;
        }

        public function group() {
            $columns = func_get_args();

            if( ! empty( $columns ) && ! empty( $columns[0] ) ) {
                foreach( $columns as $column ) {
                    $this->group[]  = $column;
                }
            }

            return $this;
        }

        public function groupClear() {
            $this->group = []; return $this;
        }

        public function groupAlias() {
            $columns = func_get_args();

            if( ! empty( $columns ) && ! empty( $columns[0] ) ) {
                foreach( $columns as $column ) {
                    $this->groupAliases[]  = $column;
                }
            }

            return $this;
        }

        public function groupAliasClear() {
            $this->groupAliases = []; return $this;
        }

        public function order() {
            $expressions = func_get_args();

            if( ! empty( $expressions ) && ! empty( $expressions[0] ) ) {
                foreach( $expressions as $expression ) {
                    $orderType      = isset( $expression[1] ) ? strtoupper( $expression[1] ) : 'ASC';
                    $this->order[]  = array( $expression[0], $orderType );
                }
            }

            return $this;
        }

        public function orderClear() {
            $this->order = []; return $this;
        }

        public function orderAlias() {
            $expressions = func_get_args();

            if( ! empty( $expressions ) && ! empty( $expressions[0] ) ) {
                foreach( $expressions as $expression ) {
                    $orderType              = isset( $expression[1] ) ? strtoupper( $expression[1] ) : 'ASC';
                    $this->orderAliases[]   = array( $expression[0], $orderType );
                }
            }

            return $this;
        }

        public function orderAliasClear() {
            $this->orderAliases = []; return $this;
        }

        public function limit() {
            $argv           = func_get_args();
            if( count( $argv ) >= 2 ) {
                list( $this->limit[0], $this->limit[1] ) = $argv;
            } else {
                $this->limit    = array( $argv[0] );
            }
            return $this;
        }

        public function escape( $string = null ) {
            return ! is_null( $string ) ? $this->connection->quote( $string ) : 'null';
        }

        public function query() {
            $this->_buildQuery();
            $this->_resetParams();
            return $this->query;
        }

        public function getQuery() {

        }

        public function setQuery( $query ) {
            $this->query    = $query;
        }

        public function _resetParams() {
            $this->tableName        = null;
            $this->selectColumns    = array();
            $this->data             = array();
            $this->buildType        = self::BUILD_TYPE_SELECT;
            $this->where            = array();
            $this->group            = array();
            $this->groupAliases     = array();
            $this->order            = array();
            $this->orderAliases     = array();
            $this->limit            = array();
        }

        private function _buildQuery() {
            switch( $this->buildType ) {
                case self::BUILD_TYPE_SELECT : {
                    $this->_buildSelectQuery(); break;
                }
                case self::BUILD_TYPE_INSERT : {
                    $this->_buildInsertQuery(); break;
                }
                case self::BUILD_TYPE_UPDATE : {
                    $this->_buildUpdateQuery(); break;
                }
                case self::BUILD_TYPE_DELETE : {
                    $this->_buildDeleteQuery(); break;
                }
            }
        }

        private function _buildSelectQuery() {
            $this->query = "SELECT %s\nFROM %s";

            if( ! empty( $this->tableName ) ) {
                $this->query    = sprintf( $this->query, $this->_buildSelectColumns(), $this->tableName );
            }

            $this->query .= $this->_buildJoins();
            $this->query .= $this->_buildWhereExpression();
            $this->query .= $this->_buildGroupByExpression();
            $this->query .= $this->_buildOrderExpression();
            $this->query .= $this->_buildLimitExpression();
        }

        private function _buildInsertQuery() {
            $this->query = "INSERT %sINTO %s\nSET ";

            if( ! empty( $this->tableName ) ) {
                $this->query    = sprintf(
                    $this->query,
                    ( $this->insertIgnore ? 'IGNORE ' : '' ),
                    $this->tableName );
            }

            $this->query .= $this->_buildSetData();
        }

        private function _buildUpdateQuery() {
            $this->query = "UPDATE %s\nSET ";

            if( ! empty( $this->tableName ) ) {
                $this->query    = sprintf( $this->query, $this->tableName );
            }

            $this->query .= $this->_buildSetData();
            $this->query .= $this->_buildWhereExpression();
            $this->query .= $this->_buildOrderExpression();
            $this->query .= $this->_buildLimitExpression();
        }

        private function _buildDeleteQuery() {
            $this->query = "DELETE FROM %s";

            if( ! empty( $this->tableName ) ) {
                $this->query    = sprintf( $this->query, $this->tableName );
            }

            $this->query .= $this->_buildWhereExpression();
            $this->query .= $this->_buildOrderExpression();
            $this->query .= $this->_buildLimitExpression();
        }

        private function _buildSelectColumns() {
            if( ! empty( $this->selectColumns ) ) {
                $stack = array();
                foreach( $this->selectColumns as $column ) {
                    if( is_object( $column ) && $column instanceOf Func ) {
                        $stack[]    = $column->getExpression();
                    } else {
                        $stack[]    = $this->_prepareColumn( $column );
                    }
                } unset( $column );
                return join( ', ', $stack );
            } else {
                return $this->tableName .'.*';
            }
        }

        private function _buildJoins() {
            $joins = null;
            if( ! empty( $this->joins ) ) {
                foreach( $this->joins as $join ) {
                    $joins .= $join->getJoinRow();
                }
            }
            return $joins;
        }

        private function _buildSetData() {
            if( ! empty( $this->data ) ) {
                $output         = array();
                foreach( $this->data as $column => $value ) {
                    $columnLongName = $this->tableName .'.'. $this->_escapeName( $column );
                    $output[]       = $columnLongName . ' = ' . $this->escape( $value );
                }
                return join( ', ' . "\n", $output );
            }
            return null;
        }

        private function _buildWhereExpression() {
            if( ! empty( $this->where ) ) {
                $stack          = array();

                foreach( $this->where as $expression ) {
                    $columnLongName = $this->_prepareColumn( $expression[0] );
                    if( ! isset( $expression[2] ) && is_array( $expression[1] ) ) {
                        $stack[]        = $columnLongName .' '. $this->_buildWhereIn( $expression[1] );
                    } else if( is_string( $expression ) ) {
                        $stack[]        = $expression;
                    } else {
                        $stack[]        = $columnLongName .' '. $expression[2] .' '. $this->escape( $expression[1] );
                    }
                }

                return ! empty( $stack ) ? "\n" . 'WHERE '. join( "\nAND\x20", $stack ) : null;
            }
            return null;
        }

        private function _buildWhereIn( array $data = [] ) {
            $output = [];
            foreach( $data as $value ) {
                $output[]   = is_numeric( $value ) ? (int) $value : $this->_escapeData( $value );
            }
            return 'IN('. implode( ', ', $output ) .')';
        }

        private function _buildGroupByExpression() {
            $stack = array();

            if( ! empty( $this->group ) ) {
                foreach( $this->group as $column ) {
                    $stack[]        = $this->tableName .'.'. $this->_escapeName( $column );
                } unset( $column );
            }

            if( ! empty( $this->groupAliases ) ) {
                foreach( $this->groupAliases as $column ) {
                    $stack[]        = $this->_escapeName( $column );
                } unset( $column );
            }

            return ! empty( $stack ) ? "\n" . 'GROUP BY '. join( ', ', $stack ) : null;
        }

        private function _buildOrderExpression() {
            $stack = array();

            if( ! empty( $this->order ) ) {
                foreach( $this->order as $expression ) {
                    $columnLongName = $this->tableName .'.'. $this->_escapeName( $expression[0] );
                    $stack[]        = $columnLongName .' '. $expression[1];
                } unset( $expression );
            }

            if( ! empty( $this->orderAliases ) ) {
                foreach( $this->orderAliases as $expression ) {
                    $stack[]        = $this->_escapeName( $expression[0] ) .' '. $expression[1];
                } unset( $expression );
            }

            return ! empty( $stack ) ? "\n" . 'ORDER BY '. join( ', ', $stack ) : null;
        }

        private function _buildLimitExpression() {
            if( ! empty( $this->limit ) ) {
                switch( $this->buildType ) {
                    case static::BUILD_TYPE_DELETE:
                    case static::BUILD_TYPE_UPDATE:
                        return isset( $this->limit[0] )
                            ? "\n" . 'LIMIT '. $this->limit[0]
                            : null;
                    case static::BUILD_TYPE_SELECT:
                        return isset( $this->limit[0] )
                            ? "\n" . 'LIMIT '. $this->limit[0] .( isset( $this->limit[1] ) ? ', '.  $this->limit[1] : null )
                            : null;
                    default:
                        return null;
                    break;
                }
            }
            return null;
        }

    }