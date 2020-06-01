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
 * Class Having
 * @package sFire\Db
 */
trait Having {


    /**
     * Contains all the having conditions
     * @var array
     */
    private array $having = [];


    /**
     * Add a new having condition
     * @param string ...$conditions
     * @return self
     */
    public function having(string ...$conditions): self {

        $this -> having = array_merge($this -> having, $conditions);
        return $this;
    }


    /**
     * Build the having condition
     * return void
     */
    private function buildHaving(): void {

        if(count($this -> having) > 0) {

            $this -> query[] = 'HAVING';
            $this -> query[] = sprintf('(%s)', implode(' AND ', $this -> having));
        }
    }
}