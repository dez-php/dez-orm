<?php

    namespace Dez\ORM\Connection;

    use Dez\ORM\Common,
        Dez\ORM\Connection,
        Dez\ORM\Exception\Error as ORMException;

    class DBO extends \PDO implements Connection\DBOInterface {

        protected
            $connectionName     = null,
            $affectedRows       = 0;

        static protected
            $config             = null,
            $schema             = null;

        public function __construct( $name ) {
            $this->connectionName   = $name;
            static::$config         = Common\Config::getInstance()->get( 'connect_'. $name );

            if ( is_null( static::$config ) ) {
                throw new ORMException( 'Invalid ORM config ('. $name .')' );
            }

            try {
                @ parent::__construct( static::$config->dsn, static::$config->user, static::$config->password, [
                    parent::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                ] );
            } catch ( \Exception $e ) {
                throw new ORMException( $e->getMessage() );
            }

            $this->setAttribute( \PDO::ATTR_ERRMODE,            \PDO::ERRMODE_EXCEPTION );
            $this->setAttribute( \PDO::ATTR_CURSOR,             \PDO::CURSOR_SCROLL );
            $this->setAttribute( \PDO::ATTR_STATEMENT_CLASS,    [ __NAMESPACE__ .'\Stmt', [ $this ] ] );

            $this->initSchema();
        }

        /**
         * @return \Dez\ORM\Connection\Schema $schema
        */

        public function getSchema() {
            return static::$schema;
        }

        public function prepareQuery( $query = null, array $params = [] ) {
            $stmt = parent::prepare( $query );
            if( count( $params ) > 0 ) {
                $stmt->bindParams( $params );
            }
            return $stmt;
        }

        public function execute( $query = null ) {
            try {
                Common\Event::instance()->dispatch( 'query', $query );
                $this->affectedRows = parent::exec( $query );
            } catch ( \Exception $e ) {
                throw new ORMException( 'Error: ('. $e->getMessage() .') Query('. $query .')' );
            }
            return $this;
        }

        /**
         * @param string $query
         * @return \Dez\ORM\Connection\Stmt $stmt
         * @throws ORMException
         */

        public function query( $query = null ) {
            try {
                Common\Event::instance()->dispatch( 'query', $query );
                return parent::query( $query );
            } catch ( \Exception $e ) {
                throw new ORMException( 'Error: ('. $e->getMessage() .') Query('. $query .')' );
            }
        }

        public function affectedRows() {
            return $this->affectedRows;
        }

        public function lastInsertId( $name = null ) {
            return parent::lastInsertId( $name );
        }

        public function transactionStart() {
            parent::beginTransaction();
            return $this;
        }

        public function commit() {
            parent::commit();
            return $this;
        }

        public function rollback() {
            parent::rollBack();
            return $this;
        }

        private function initSchema() {
            $schemaFile     = Common\Config::getInstance()->get( 'schema_file', null );
            try {
                static::$schema   = new Schema( $schemaFile );
            } catch( ORMException $e ) {
                throw $e;
            }
        }

    }