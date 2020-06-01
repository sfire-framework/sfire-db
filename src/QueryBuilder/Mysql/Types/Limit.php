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
 * Class Limit
 * @package sFire\Db
 */
trait Limit {


    /**
     * Contains all the limit conditions
     * @var int
     */
    private ?int $limit = null;


    /**
     * Add a new limit condition
     * @param int $amount
     * @return self
     */
    public function limit(int $amount): self {

        $this -> limit = $amount;
        return $this;
    }


    /**
     * Build the limit condition
     * return void
     */
    private function buildLimit(): void {

        if(null !== $this -> limit) {
            $this -> query[] = sprintf('LIMIT %s', $this -> limit);
        }
    }
}