<?php

    namespace Dez\ORM\Relation;

    class HasOne extends Relation {

        protected function makeRelation() {
            $related            = $this->related;
            $this->collection   = $related::query()->where( $this->foreignKey, $this->ids )->find();
        }

    }