<?php

    namespace Dez\ORM\Relation;

    class HasMany extends Relation {

        protected function makeRelation() {
            $related            = $this->related;
            $this->collection   = $related::query()->where( $this->foreignKey, $this->ids )->find();
        }

    }