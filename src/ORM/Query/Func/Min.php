<?php

    namespace Dez\ORM\Query\Func;

    use \Dez\ORM\Query;

    class Min extends Query\Func {
        protected function _createExpression() {
            $this->expression   = 'MIN('. $this->tableName .'.'. $this->columnName .')';
            return $this;
        }
    }