<?php

    namespace Dez\ORM\Common;

    use \Dez\ORM\Entity;

    class Pagi {

        protected
            $currentPage    = 0,
            $offset         = 0,
            $totalPages     = 0,
            $perPage        = 0;

        public function __construct( $currentPage = 0, $perPage = 0, $numRows = 0 ) {
            $this->perPage      = $perPage;
            $this->totalPages   = ceil( $numRows / $perPage );
            $this->currentPage  = min( ( 1 >= $currentPage ? 1 : $currentPage ), $this->totalPages );
            $this->offset       = min( $numRows, abs( ( $this->currentPage - 1 ) * $this->perPage ) );
        }

        public function getOffset() {
            return (int) $this->offset;
        }

        public function getLength() {
            return (int) $this->perPage;
        }

        public function getCurrentPage() {
            return (int) $this->currentPage;
        }

        public function getNumPages() {
            return (int) $this->totalPages;
        }

    }