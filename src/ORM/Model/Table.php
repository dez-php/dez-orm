<?php

    namespace Dez\ORM\Model;

    use Dez\ORM\Collection\ModelCollection;
    use Dez\ORM\Query\Builder as BaseQueryBuilder;

    class Table extends TableAbstract {

        public function __destruct() {
            $this->onDestroy();
        }

        /**
         * @return QueryBuilder $builder
        */

        static public function query() {
            return new QueryBuilder( new static );
        }

        /**
         * @return ModelCollection $collection
         */

        static public function all() {
            return static::query()->find();
        }

        /**
         * @return static
         * @param int $id
        */

        static public function one( $id = 0 ) {
            return static::query()->findOne( $id );
        }

        /**
         * @param array $data
         * @return static
         */

        static public function insert( array $data = [], $ignore = false ) {
            $model  = new static();
            $model->bind( $data )->save( $ignore );
            return $model;
        }

        public function save( $ignore = false ) {
            $this->beforeSave();
            $query      = new QueryBuilder( $this );
            $result     = $this->exists() ? $query->update() : $this->id = ( $ignore ? $query->ignore()->insert() : $query->insert() );
            $this->afterSave();
            return $result;
        }

        public function delete() {
            $this->beforeDelete();
            $query      = new QueryBuilder( $this );
            $result     = $this->exists() ? $query->delete() : 0;
            $this->afterDelete();
            return $result;
        }

        public function id() {
            return $this->id;
        }

        public function pk() {
            return $this->pk;
        }

        public function toArray() {
            return (array) $this->data;
        }

        public function toObject() {
            return (object) $this->data;
        }

        public function toJSON() {
            return json_encode( $this->toArray() );
        }

        protected function beforeSave() {}

        protected function beforeDelete() {}

        protected function afterSave() {}

        protected function afterDelete() {}

        protected function onDestroy() {}

    }