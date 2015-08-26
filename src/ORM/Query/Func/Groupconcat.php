<?php

    namespace Dez\ORM\Query\Func;

    use \Dez\ORM\Query;

    class Groupconcat extends Query\Func {
        protected function _createExpression() {
            $this->expression   = 'GROUP_CONCAT('. $this->column['table'] .'.'. $this->column['name'] .')';
            return $this;
        }
    }