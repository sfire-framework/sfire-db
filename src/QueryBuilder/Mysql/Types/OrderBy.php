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
 * Class OrderBy
 * @package sFire\Db
 */
trait OrderBy {


    /**
     * Contains all the order by conditions
     * @var array
     */
    private array $orderBy = [];


    /**
     * Add a new order by condition
     * @param string $column
     * @param string $sort Sorting method i.e. ASC or DESC
     * @return self
     */
    public function orderBy(string $column, string $sort = 'ASC'): self {

        $this -> orderBy[] = ['column' => $column, 'sort' => strtoupper($sort)];
        return $this;
    }


    /**
     * Build the order by condition
     * return void
     */
    private function buildOrderBy(): void {

        if(count($this -> orderBy) > 0) {

            $this -> query[] = 'ORDER BY';

            foreach($this -> orderBy as $orderBy) {
                $this -> query[] = sprintf('%s %s', $orderBy['column'], $orderBy['sort']);
            }
        }
    }
}