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
 * Class GroupBy
 * @package sFire\Db
 */
trait GroupBy {


    /**
     * Contains all the group by conditions
     * @var array
     */
    private array $groupBy = [];


    /**
     * Add a new group by condition
     * @param string ...$columns
     * @return self
     */
    public function groupBy(string ...$columns): self {

        $this -> groupBy = array_merge($this -> groupBy, $columns);
        return $this;
    }


    /**
     * Build the group by condition
     * return void
     */
    private function buildGroupBy(): void {

        if(count($this -> groupBy) > 0) {

            $this -> query[] = 'GROUP BY';
            $this -> query[] = implode(', ', $this -> groupBy);
        }
    }
}