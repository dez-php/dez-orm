<?php

namespace Dez\ORM\Query;

use Dez\ORM\Error as ORMException;
use Dez\ORM\Query;

/**
 * Class ActiveQuery
 * @package Dez\ORM\Query
 */
class ActiveQuery
{

    /**
     * @var Builder|null
     */
    protected $builder = null;

    /**
     * @param Builder $builder
     */
    public function __construct(Query\Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param null $table
     * @param array $where
     * @param array $groupBy
     * @param array $orderBy
     * @return \Dez\Db\Stmt
     * @throws \Dez\Db\Exception
     */
    public function select($table = null, array $where = [], array $groupBy = [], array $orderBy = [])
    {
        $this->builder
            ->select()
            ->table($table)
            ->group($groupBy)
            ->order($orderBy);
        $this->_where($where);

        return $this->builder->getConnection()->query($this->builder->query());
    }

    /**
     * @param array $where
     */
    private function _where(array $where = [])
    {
        if (count($where) != count($where, true) && is_numeric(key($where))) {
            foreach ($where as $expression) {
                $this->builder->where($expression);
            }
        } else {
            $this->builder->where($where);
        }
    }

    /**
     * @param null $table
     * @param array $data
     * @return \Dez\Db\Connection
     * @throws \Dez\Db\Exception
     */
    public function insert($table = null, array $data = [])
    {
        $query = $this->builder
            ->insert()
            ->table($table)
            ->bind($data)
            ->query();

        return $this->builder->getConnection()->execute($query);
    }

    /**
     * @param null $table
     * @param array $data
     * @param array $where
     * @return \Dez\Db\Connection
     * @throws \Dez\Db\Exception
     */
    public function update($table = null, array $data = [], array $where = [])
    {
        $this->builder
            ->update()
            ->table($table)
            ->bind($data);
        $this->_where($where);

        return $this->builder->getConnection()->execute($this->builder->query());
    }

    /**
     * @param null $table
     * @param array $where
     * @return \Dez\Db\Connection
     * @throws \Dez\Db\Exception
     */
    public function delete($table = null, array $where = [])
    {
        $this->builder
            ->delete()
            ->table($table);
        $this->_where($where);

        return $this->builder->getConnection()->execute($this->builder->query());
    }

}