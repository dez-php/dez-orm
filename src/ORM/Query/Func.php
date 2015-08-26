<?php

    namespace Dez\ORM\Query;

    abstract class Func {

        use BuilderTrait;

        protected
            $tableName  = null,
            $columnName = null,
            $args       = array(),

            $column     = array(),

            $expression = null;

        public function wrap( $tableName = null, $columnName = null, array $args = [] ) {
            $this->tableName    = $tableName;
            $this->columnName   = $columnName;
            $this->args         = $args;
            return $this;
        }

        public function getExpression() {
            $this->_prepare()->_createExpression()->_addAlias();
            return $this->expression;
        }

        private function _prepare() {
            list(
                $this->column['table'],
                $this->column['name'],
                $this->column['alias']
                ) = $this->_prepareColumn( $this->columnName, true );
            return $this;
        }

        protected function _addAlias() {
            if( ! empty( $this->column['alias'] ) ) {
                $this->expression .= ' '. $this->column['alias'];
            }
        }

        abstract protected function _createExpression();

    }