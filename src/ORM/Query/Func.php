<?php

    namespace Dez\ORM\Query;

    /**
     * Class Func
     * @package Dez\ORM\Query
     */
    abstract class Func {

        use BuilderTrait;

        /**
         * @var null
         */
        protected $tableName  = null;
        /**
         * @var null
         */
        protected $columnName = null;
        /**
         * @var array
         */
        protected $args       = [];

        /**
         * @var array
         */
        protected $column     = [];

        /**
         * @var null
         */
        protected $expression = null;

        /**
         * @param null $tableName
         * @param null $columnName
         * @param array $args
         * @return $this
         */
        public function wrap( $tableName = null, $columnName = null, array $args = [] ) {
            $this->tableName    = $tableName;
            $this->columnName   = $columnName;
            $this->args         = $args;
            return $this;
        }

        /**
         * @return null
         */
        public function getExpression() {
            $this->_prepare()->_createExpression()->_addAlias();
            return $this->expression;
        }

        /**
         * @return $this
         */
        private function _prepare() {
            list(
                $this->column['table'],
                $this->column['name'],
                $this->column['alias']
                ) = $this->_prepareColumn( $this->columnName, true );
            return $this;
        }

        /**
         *
         */
        protected function _addAlias() {
            if( ! empty( $this->column['alias'] ) ) {
                $this->expression .= ' '. $this->column['alias'];
            }
        }

        /**
         * @return mixed
         */
        abstract protected function _createExpression();

    }