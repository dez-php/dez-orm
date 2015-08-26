<?php

    namespace Dez\ORM\Relation;

    use Dez\ORM\Common\Object;
    use Dez\ORM\Common\SingletonTrait;
    use Dez\ORM\Model\Table as TableModel;

    abstract class Relation extends Object {

        use SingletonTrait;

        protected
            $model          = null,
            $related        = null,
            $foreignKey     = 'id',
            $ids            = [ 0 ],

            $collection     = null;

        /**
         * @param   array $ids
         * @param   TableModel $related
         * @param   string $foreignKey
         */

        protected function init( array $ids = [], $related, $foreignKey ) {
            $this->ids          = $ids;
            $this->related      = $related;
            $this->foreignKey   = $foreignKey;
            $this->makeRelation();
        }

        abstract protected function makeRelation();

        public function setModel( TableModel $model ) {
            $this->model    = $model;
            return $this;
        }

        public function get() {
            return $this->collection->findAll( function( $item ) {
                return $this->model->id() == $item->get( $this->foreignKey );
            } );
        }

    }