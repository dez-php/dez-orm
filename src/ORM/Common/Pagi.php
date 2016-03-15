<?php

namespace Dez\ORM\Common;

class Pagi
{

    protected $currentPage = 0;
    protected $offset = 0;
    protected $totalPages = 0;
    protected $perPage = 0;

    /**
     * Pagi constructor.
     * @param int $currentPage
     * @param int $perPage
     * @param int $numRows
     */
    public function __construct($currentPage = 0, $perPage = 0, $numRows = 0)
    {
        $this->perPage = $perPage;
        $this->totalPages = ceil($numRows / $perPage);
        $this->currentPage = min((1 >= $currentPage ? 1 : $currentPage), $this->totalPages);
        $this->offset = min($numRows, abs(($this->currentPage - 1) * $this->perPage));
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return (int)$this->offset;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return (int)$this->perPage;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return (int)$this->currentPage;
    }

    /**
     * @return int
     */
    public function getNumPages()
    {
        return (int)$this->totalPages;
    }

}