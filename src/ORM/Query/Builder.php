<?php

    namespace Dez\ORM\Query;

    use Dez\ORM\Common\Event;
    use Dez\ORM\Exception as ORMException;
    use Dez\Db\Connection;

    /**
     * Class Builder
     * @package Dez\ORM\Query
     */
    class Builder {

        use BuilderTrait;

        const BUILD_TYPE_SELECT   = 1;

        const BUILD_TYPE_UPDATE   = 2;

        const BUILD_TYPE_INSERT   = 3;

        const BUILD_TYPE_DELETE   = 4;

        /**
         * @var Connection
         */
        protected $connection;

        /**
         * @var string
         */
        protected $query              = '';
        /**
         * @var string
         */
        protected $tableName          = '';
        /**
         * @var array
         */
        protected $data               = [];
        /**
         * @var int
         */
        protected $buildType          = self::BUILD_TYPE_SELECT;

        /**
         * @var array
         */
        protected $selectColumns      = [];

        /**
         * @var bool
         */
        protected $insertIgnore       = false;

        /**
         * @var array
         */
        protected $where              = [];

        /**
         * @var array
         */
        protected $group              = [];
        /**
         * @var array
         */
        protected $groupAliases       = [];

        /**
         * @var array
         */
        protected $order              = [];
        /**
         * @var array
         */
        protected $orderAliases       = [];

        /**
         * @var array
         */
        protected $limit              = [];

        /**
         * @var array
         */
        protected $joins              = [];

        /**
         * @var array
         */
        private static
            $cmpTypes   = array( '=', '>', '<', '>=', '<=', '!=', '<>' );

        /**
         * @param Connection $connection
         */
        public function __construct( Connection $connection ) {
            $this->connection = $connection;
        }

        /**
         * @return string
         */
        public function __toString() {
            return (string) $this->query();
        }

        /**
         * @param $tableName
         */
        public function __invoke( $tableName ) {
            $this->table( $tableName );
        }

        /**
         * @return Connection|null
         */
        public function getConnection() {
            return $this->connection;
        }

        /**
         * @param null $functionName
         * @param null $column
         * @param array $sqlFuncArgs
         * @return \stdClass
         * @throws ORMException
         */
        public function func( $functionName = null, $column = null, array $sqlFuncArgs = [] ) {
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

        /**
         * @param $type
         * @param $table
         * @param $joinTable
         * @param array $expression
         * @return $this
         */
        public function join( $type, $table, $joinTable, array $expression = [] ) {
            $this->joins[] = new Join(
                $type,
                $this->_escapeName( $table ),
                $this->_escapeName( $joinTable ),
                $expression
            );
            return $this;
        }

        /**
         * @param $table
         * @param $joinTable
         * @param array $expression
         * @return Builder
         */
        public function innerJoin( $table, $joinTable, array $expression = [] ) {
            return $this->join( 'inner', $table, $joinTable, $expression );
        }

        /**
         * @param $table
         * @param $joinTable
         * @param array $expression
         * @return Builder
         */
        public function leftJoin( $table, $joinTable, array $expression = [] ) {
            return $this->join( 'left', $table, $joinTable, $expression );
        }

        /**
         * @param $table
         * @param $joinTable
         * @param array $expression
         * @return Builder
         */
        public function rightJoin( $table, $joinTable, array $expression = [] ) {
            return $this->join( 'right', $table, $joinTable, $expression );
        }

        /**
         * @param array $data
         * @return $this
         */
        public function bind( array $data = [] ) {
            if( ! empty( $data ) ) {
                foreach( $data as $key => $value ) {
                    $this->data[$key] = $value;
                }
            }
            return $this;
        }

        /**
         * @param array $columns
         * @param bool|true $merge
         * @return $this
         */
        public function select( array $columns = [], $merge = true ) {
            $this->buildType = self::BUILD_TYPE_SELECT;
            if( ! empty( $columns ) ) {
                $this->selectColumns = ! $merge ? $columns : array_merge( $this->selectColumns, $columns );
            }
            return $this;
        }

        /**
         * @return $this
         */
        public function update() {
            $this->buildType = self::BUILD_TYPE_UPDATE;
            return $this;
        }

        /**
         * @return $this
         */
        public function insert() {
            $this->buildType = self::BUILD_TYPE_INSERT;
            return $this;
        }

        /**
         * @return $this
         */
        public function ignore() {
            $this->insertIgnore = true;
            return $this;
        }

        /**
         * @return $this
         */
        public function delete() {
            $this->buildType = self::BUILD_TYPE_DELETE;
            return $this;
        }

        /**
         * @param null $tableName
         * @return $this
         */
        public function table( $tableName = null ) {
            $this->tableName = $this->_escapeName( $tableName );
            return $this;
        }

        /**
         * @return $this
         */
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

        /**
         * @param string $query
         * @return $this
         */
        public function whereRaw( $query = '' ) {
            $this->where[]  = $query;
            return $this;
        }

        /**
         * @return $this
         */
        public function group() {
            $columns = func_get_args();

            if( ! empty( $columns ) && ! empty( $columns[0] ) ) {
                foreach( $columns as $column ) {
                    $this->group[]  = $column;
                }
            }

            return $this;
        }

        /**
         * @return $this
         */
        public function groupClear() {
            $this->group = []; return $this;
        }

        /**
         * @return $this
         */
        public function groupAlias() {
            $columns = func_get_args();

            if( ! empty( $columns ) && ! empty( $columns[0] ) ) {
                foreach( $columns as $column ) {
                    $this->groupAliases[]  = $column;
                }
            }

            return $this;
        }

        /**
         * @return $this
         */
        public function groupAliasClear() {
            $this->groupAliases = []; return $this;
        }

        /**
         * @return $this
         */
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

        /**
         * @return $this
         */
        public function orderClear() {
            $this->order = []; return $this;
        }

        /**
         * @return $this
         */
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

        /**
         * @return $this
         */
        public function orderAliasClear() {
            $this->orderAliases = []; return $this;
        }

        /**
         * @return $this
         */
        public function limit() {
            $argv           = func_get_args();
            if( count( $argv ) >= 2 ) {
                list( $this->limit[0], $this->limit[1] ) = $argv;
            } else {
                $this->limit    = array( $argv[0] );
            }
            return $this;
        }

        /**
         * @param null $string
         * @return string
         */
        public function escape( $string = null ) {
            return ! is_null( $string ) ? $this->connection->quote( $string ) : 'null';
        }

        /**
         * @return string|null
         * @throws ORMException
         */
        public function query() {
            $this->_buildQuery();
            $this->_resetParams();

            Event::instance()->dispatch( 'query', $this->query );

            return $this->query;
        }

        /**
         * @return string
         */
        public function getQuery() {
            return $this->query;
        }

        /**
         * @param $query
         * @return static
         */
        public function setQuery( $query ) {
            $this->query    = $query;
            return $this;
        }

        /**
         * @return $this
         */
        public function _resetParams() {
            $this->tableName        = null;
            $this->selectColumns    = [];
            $this->data             = [];
            $this->buildType        = self::BUILD_TYPE_SELECT;
            $this->where            = [];
            $this->group            = [];
            $this->groupAliases     = [];
            $this->order            = [];
            $this->orderAliases     = [];
            $this->limit            = [];
            return $this;
        }

        /**
         * @return static
         */
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
            return $this;
        }

        /**
         * @return static
         */
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

            return $this;
        }

        /**
         * @return static
         */
        private function _buildInsertQuery() {
            $this->query = "INSERT %sINTO %s\nSET ";

            if( ! empty( $this->tableName ) ) {
                $this->query    = sprintf(
                    $this->query,
                    ( $this->insertIgnore ? 'IGNORE ' : '' ),
                    $this->tableName );
            }

            $this->query .= $this->_buildSetData();

            return $this;
        }

        /**
         * @return static
         */
        private function _buildUpdateQuery() {
            $this->query = "UPDATE %s\nSET ";

            if( ! empty( $this->tableName ) ) {
                $this->query    = sprintf( $this->query, $this->tableName );
            }

            $this->query .= $this->_buildSetData();
            $this->query .= $this->_buildWhereExpression();
            $this->query .= $this->_buildOrderExpression();
            $this->query .= $this->_buildLimitExpression();

            return $this;
        }

        /**
         * @return static
         */
        private function _buildDeleteQuery() {
            $this->query = "DELETE FROM %s";

            if( ! empty( $this->tableName ) ) {
                $this->query    = sprintf( $this->query, $this->tableName );
            }

            $this->query .= $this->_buildWhereExpression();
            $this->query .= $this->_buildOrderExpression();
            $this->query .= $this->_buildLimitExpression();

            return $this;
        }

        /**
         * @return string
         */
        private function _buildSelectColumns() {
            if( ! empty( $this->selectColumns ) ) {
                $stack = [];
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

        /**
         * @return null|string
         */
        private function _buildJoins() {
            $joins = null;
            if( ! empty( $this->joins ) ) {
                foreach( $this->joins as $join ) {
                    $joins .= $join->getJoinRow();
                }
            }
            return $joins;
        }

        /**
         * @return null|string
         */
        private function _buildSetData() {
            if( ! empty( $this->data ) ) {
                $output         = [];
                foreach( $this->data as $column => $value ) {
                    $columnLongName = $this->tableName .'.'. $this->_escapeName( $column );
                    $output[]       = $columnLongName . ' = ' . $this->escape( $value );
                }
                return join( ', ' . "\n", $output );
            }
            return null;
        }

        /**
         * @return null|string
         */
        private function _buildWhereExpression() {
            if( ! empty( $this->where ) ) {
                $stack          = [];

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

        /**
         * @param array $data
         * @return string
         */
        private function _buildWhereIn( array $data = [] ) {
            $output = [];
            foreach( $data as $value ) {
                $output[]   = is_numeric( $value ) ? (int) $value : $this->_escapeData( $value );
            }
            return 'IN('. implode( ', ', $output ) .')';
        }

        /**
         * @return null|string
         */
        private function _buildGroupByExpression() {
            $stack = [];

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

        /**
         * @return null|string
         */
        private function _buildOrderExpression() {
            $stack = [];

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

        /**
         * @return null|string
         */
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