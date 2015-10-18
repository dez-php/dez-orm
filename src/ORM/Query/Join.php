<?php

    namespace Dez\ORM\Query;

    /**
     * Class Join
     * @package Dez\ORM\Query
     */
    class Join {

        use BuilderTrait;

        /**
         * @var null
         */
        protected $type       = null;
        /**
         * @var null
         */
        protected $cmpTable   = null;
        /**
         * @var null
         */
        protected $joinTable  = null;
        /**
         * @var array
         */
        protected $expression = [];

        /**
         * @param null $type
         * @param null $joinTable
         * @param null $cmpTable
         * @param array $onExpression
         */
        public function __construct( $type = null, $joinTable = null, $cmpTable = null, array $onExpression = [] ) {
            $this->type         = $type;
            $this->cmpTable     = $cmpTable;
            $this->joinTable    = $joinTable;
            $this->expression   = $onExpression;
        }

        /**
         * @return string
         */
        public function getJoinRow() {
            return $this->_buildJoin();
        }

        /**
         * @return string
         */
        private function _buildJoin() {
            $query = "\n" . '%s JOIN %s ON %s %s %s';
            return sprintf(
                $query,
                strtoupper( $this->type ),
                $this->joinTable,
                $this->cmpTable .'.'. $this->_escapeName( $this->expression[1] ),
                isset( $this->expression[2] ) ? $this->expression[2] : '=',
                $this->joinTable .'.'. $this->_escapeName( $this->expression[0] )
            );
        }

    }

