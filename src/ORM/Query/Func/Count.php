<?php

    namespace Dez\ORM\Query\Func;

    use \Dez\ORM\Query;

    class Count extends Query\Func {
        protected function _createExpression() {
            $countExpr = ! $this->column['name'] ? '*' : $this->column['table'] .'.'. $this->column['name'];
            if( isset( $this->args[0] ) && ! empty( $this->args[0] ) )
                $countExpr = strtoupper( $this->args[0] ) . ' ' . $countExpr;
            $this->expression   = 'COUNT('. $countExpr .')';
            return $this;
        }
    }