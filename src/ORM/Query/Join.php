<?php

    namespace Dez\ORM\Query;

    class Join {

        use BuilderTrait;

        private
            $type       = null,
            $cmpTable   = null,
            $joinTable  = null,
            $expression = [];

        public function __construct( $type = null, $joinTable = null, $cmpTable = null, array $onExpression = [] ) {
            $this->type         = $type;
            $this->cmpTable     = $cmpTable;
            $this->joinTable    = $joinTable;
            $this->expression   = $onExpression;
        }

        public function getJoinRow() {
            return $this->_buildJoin();
        }

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

