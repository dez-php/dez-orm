<?php

    namespace Dez\ORM\Model;

    use Dez\Error\Exception\InvalidArgs;
    use Dez\ORM;
    use Dez\ORM\Common\Object;
    use Dez\ORM\Common\Utils;
    use Dez\ORM\Connection\DBO as DbConnection;
    use Dez\ORM\Connection\Stmt;
    use Dez\ORM\Exception\Error as ORMException;
    use Dez\ORM\Collection\ModelCollection;
    use Dez\ORM\Common\Pagi as Pagination;

    use Dez\ORM\Relation\HasMany as RelationHasMany;
    use Dez\ORM\Relation\HasOne as RelationHasOne;

    /**
     * @Injectable(lazy=true)
     */

    abstract class TableAbstract extends Object {

        protected $connection     = null;
        protected $data           = [];
        protected $pk             = null;
        protected $id             = 0;

        protected $collection     = null;
        protected $pagination     = null;

        /**
         * @throws ORMException
         */

        public function __construct() {
            if( ! $this->hasTable() ) {
                throw new ORMException( 'Not defined table name for: '. $this->getTableName() );
            }
            $this->setConnection( ORM::connect() );
            $this->pk   = $this->getConnection()->getSchema()->getTablePK( $this->getTableName() );
        }

        /**
         * @return static
         * @throws ORMException
         */

        public function __call( $name, $args ) {

            $methodName     = substr( $name, 0, 3 );
            $columnName     = substr( $name, 3 );
            $columnValue    = isset( $args[0] ) ? $args[0] : null;

            switch( $methodName ) {
                case 'set': {
                    return $this->set( $this->getSQLName( $columnName ), $columnValue );
                    break;
                }
                case 'get': {
                    return $this->get( $this->getSQLName( $columnName ) );
                    break;
                }
                default: {
                    throw new ORMException( 'Call undefined method' );
                }
            }
        }

        /**
         * @return mixed
         * @param string $name
         */

        public function __get( $name = null ) {
            return $this->get( $name );
        }

        /**
         * @return static
         * @param string $name
         * @param string $value
         */

        public function __set( $name = null, $value = null ) {
            return $this->set( $name, $value );
        }

        /**
         * @return mixed
         * @param string $name
         * @param mixed $default
         */

        public function get( $name, $default = null ) {
            return isset( $this->data[$name] ) ? $this->data[$name] : $default;
        }

        /**
         * @return static
         */

        public function set( $name = null, $value = null ) {
            if( $this->pk == $name ) {
                $this->id           = $value;
            } else {
                $this->data[$name]  = $value;
            }
            return $this;
        }

        /**
         * @return static
         */

        public function bind( array $data = [] ) {
            foreach( $data as $key => $value )
                $this->$key     = $value;
            return $this;
        }

        /**
         * @return string $tableName
         */

        public function getTableName() {
            return ! $this->hasTable() ?: static::$table;
        }

        /**
         * @return static
         * @param DbConnection $connection
         */

        public function setConnection( DbConnection $connection ) {
            $this->connection   = $connection;
            return $this;
        }

        /**
         * @return DbConnection $connection
         */

        public function getConnection() {
            return $this->connection;
        }

        /**
         * @return ModelCollection $collection
         * @param Stmt $stmt
        */

        public function createCollection( Stmt $stmt ) {
            $collection = new ModelCollection();
            $collection->setType( $this->getClassName() );
            $pagination = $this->getPagination();
            while( $model = $stmt->loadIntoObject( $this->getClassName() ) ) {
                $model->setCollection( $collection );
                $collection->add( $model );
                ! $pagination ?: $model->setPagination( $pagination );
            }
            return $collection;
        }

        /**
         * @param ModelCollection $collection
         * @return static
         */

        public function setCollection( ModelCollection $collection ) {
            $this->collection   = $collection;
            return $this;
        }

        /**
         * @return ModelCollection $collection
        */

        public function getCollection() {
            return $this->collection;
        }

        /**
         * @param Pagination $pagination
         * @return static
         */

        public function setPagination( Pagination $pagination ) {
            $this->pagination   = $pagination;
            return $this;
        }

        /**
         * @return Pagination $pagination
         */

        public function getPagination() {
            return $this->pagination;
        }

        /**
         * @return boolean
        */

        public function exists() {
            return ( $this->id() > 0 );
        }

        /**
         * @return boolean
        */

        protected function hasTable() {
            return property_exists( $this->getClassName(), 'table' );
        }

        /**
         * @return string|boolean $table
        */

        static public function table() {
            return static::$table;
        }

        /**
         * @return string $name
         */

        protected function getSQLName( $phpName = null ) {
            return ! $phpName ? null : Utils::php2sql( $phpName );
        }

        /**
         * @param   string $related
         * @param   string $foreignKey
         * @return  static $model
         * @throws  InvalidArgs
         */

        protected function hasOne( $related = null, $foreignKey = 'id' ) {
            if( $related != null && class_exists( $related ) ) {
                $collection  = RelationHasOne::instance( $this->getCollection()->getIDs(), $related, $foreignKey )->setModel( $this )->get();
                return $collection->count() > 0 ? $collection[0] : new $related;
            }
            throw new InvalidArgs( 'Related model not found ['. $related .']' );
        }

        /**
         * @param   string $related
         * @param   string $foreignKey
         * @return  ModelCollection $collection
         * @throws  InvalidArgs
        */

        protected function hasMany( $related = null, $foreignKey = 'id' ) {
            if( $related != null && class_exists( $related ) ) {
                $ids = $this->getCollection() ? $this->getCollection()->getIDs() : [ $this->id() ];
                return RelationHasMany::instance( $ids, $related, $foreignKey )->setModel( $this )->get();
            }
            throw new InvalidArgs( 'Related model not found ['. $related .']' );
        }

        /**
         * @return int $id
        */

        abstract public function id();

    }