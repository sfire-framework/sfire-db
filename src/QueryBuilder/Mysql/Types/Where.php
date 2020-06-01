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
 * Class Where
 * @package sFire\Db
 */
trait Where {


    /**
     * Contains all the where conditions
     * @var array
     */
    private array $where = [];


    /**
     * Add a new where condition
     * @param string[] $conditions
     * @return self
     */
    public function where(string ...$conditions): self {

        $this -> where = array_merge($this -> where, $conditions);
        return $this;
    }


    /**
     * Build the where condition
     * return void
     */
    private function buildWhere(): void {

        if(count($this -> where) > 0) {

            $this -> query[] = 'WHERE';
            $this -> query[] = sprintf('(%s)', implode(' AND ', $this -> where));
        }
    }
}