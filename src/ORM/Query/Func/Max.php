<?php

    namespace Dez\ORM\Query\Func;

    use \Dez\ORM\Query;

    class Max extends Query\Func {
        protected function _createExpression() {
            $this->expression   = 'MAX('. $this->tableName .'.'. $this->columnName .')';
            return $this;
        }
    }