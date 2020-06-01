<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Db\QueryBuilder\Mysql\Types;


/**
 * Class Joins
 * @package sFire\Db
 */
trait Joins {


    /**
     * Contains all the left join conditions
     * @var array
     */
    private array $joins = [];


    /**
     * Add a left join clause
     * @param string $tableName
     * @param null|string $tableAlias
     * @param null|string $conditions
     * @return self
     */
    public function leftJoin(string $tableName, string $tableAlias = null, string $conditions = null): self {
        return $this -> addJoin('left', $tableName, $tableAlias, $conditions);
    }


    /**
     * Add a right join clause
     * @param string $tableName
     * @param null|string $tableAlias
     * @param null|string $conditions
     * @return self
     */
    public function rightJoin(string $tableName, string $tableAlias = null, string $conditions = null): self {
        return $this -> addJoin('right', $tableName, $tableAlias, $conditions);
    }


    /**
     * Add a inner join clause
     * @param string $tableName
     * @param null|string $tableAlias
     * @param null|string $conditions
     * @return self
     */
    public function innerJoin(string $tableName, string $tableAlias = null, string $conditions = null): self {
        return $this -> addJoin('inner', $tableName, $tableAlias, $conditions);
    }


    /**
     * Add a cross join clause
     * @param string $tableName
     * @param null|string $tableAlias
     * @return self
     */
    public function crossJoin(string $tableName, string $tableAlias = null): self {
        return $this -> addJoin('cross', $tableName, $tableAlias);
    }


    /**
     * Generic method for adding a join clause
     * @param string $type
     * @param string $tableName
     * @param null|string $tableAlias
     * @param null|string $conditions
     * @return self
     */
    private function addJoin(string $type, string $tableName, string $tableAlias = null, string $conditions = null): self {

        $this -> joins[] = ['table' => $tableName, 'alias' => $tableAlias, 'conditions' => $conditions, 'type' => $type];
        return $this;
    }


    /**
     * Build a left/right/inner/outer join clause
     * @return void
     */
    private function buildJoins(): void {

        if(count($this -> joins) > 0) {

            $query = [];

            foreach($this -> joins as $join) {

                $parts   = [];
                $parts[] = sprintf('%s JOIN', strtoupper($join['type']));
                $parts[] = $join['table'];

                if(null !== $join['alias']) {
                    $parts[] = $join['alias'];
                }

                if(null !== $join['conditions']) {
                    $parts[] = sprintf('ON %s', $join['conditions']);
                }

                $query[] = implode(' ', $parts);
            }

            $this -> query[] = implode(' ', $query);
        }
    }
}