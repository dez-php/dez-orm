<?php

    namespace Dez\ORM\Query;

    use \Dez\ORM\Exception\Error as ORMException,
        \Dez\ORM\Query;

    class ActiveQuery {

        private
            $builder = null;

        public function __construct( Query\Builder $builder ) {
            $this->builder = $builder;
        }

        public function select( $table = null, array $where = [], array $groupBy = [], array $orderBy = [] ) {
            $this->builder
                ->select()
                ->table( $table )
                ->group( $groupBy )
                ->order( $orderBy );
            $this->_where( $where );
            return $this->builder->getConnection()->query( $this->builder->query() );
        }

        public function insert( $table = null, array $data = [] ) {
            $query = $this->builder
                ->insert()
                ->table( $table )
                ->bind( $data )
                ->query();
            return $this->builder->getConnection()->execute( $query );
        }

        public function update( $table = null, array $data = [], array $where = [] ) {
            $this->builder
                ->update()
                ->table( $table )
                ->bind( $data );
            $this->_where( $where );
            return $this->builder->getConnection()->execute( $this->builder->query() );
        }

        public function delete( $table = null, array $where = [] ) {
            $this->builder
                ->delete()
                ->table( $table );
            $this->_where( $where );
            return $this->builder->getConnection()->execute( $this->builder->query() );
        }

        private function _where( array $where = [] ) {
            if( count( $where ) != count( $where, true ) && is_numeric( key( $where ) ) ) {
                foreach( $where as $expression ) {
                    $this->builder->where( $expression );
                }
            } else {
                $this->builder->where( $where );
            }
        }

    }