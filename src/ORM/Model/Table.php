<?php

    namespace Dez\ORM\Model;

    use Dez\ORM\Collection\ModelCollection;

    class Table extends TableAbstract {

        /**
         *
         */
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

        /**
         * @param bool|false $ignore
         * @return bool|int
         */
        public function save( $ignore = false ) {
            $this->beforeSave();
            $query      = new QueryBuilder( $this );
            $result     = $this->exists() ? $query->update() : $this->id = ( $ignore ? $query->ignore()->insert() : $query->insert() );
            $this->afterSave();
            return $result;
        }

        /**
         * @return bool|int
         */
        public function delete() {
            $this->beforeDelete();
            $query      = new QueryBuilder( $this );
            $result     = $this->exists() ? $query->delete() : 0;
            $this->afterDelete();
            return $result;
        }

        /**
         * @return int
         */
        public function id() {
            return $this->id;
        }

        /**
         * @return mixed|null
         */
        public function pk() {
            return $this->definePk() ? static::$pk : 'id';
        }

        /**
         * @return array
         */
        public function toArray() {
            return (array) $this->data;
        }

        /**
         * @return object
         */
        public function toObject() {
            return (object) $this->data;
        }

        /**
         * @return string
         */
        public function toJSON() {
            return json_encode( $this->toArray() );
        }

        /**
         *
         */
        protected function beforeSave() {}

        /**
         *
         */
        protected function beforeDelete() {}

        /**
         *
         */
        protected function afterSave() {}

        /**
         *
         */
        protected function afterDelete() {}

        /**
         *
         */
        protected function onDestroy() {}

    }