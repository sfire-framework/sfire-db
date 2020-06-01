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
 * Class Distinct
 * @package sFire\Db
 */
trait Distinct {


    /**
     * Contains if errors should be distinct
     * @var bool
     */
    private bool $distinct = false;


    /**
     * Sets if only distinct (different) values should return
     * @return self
     */
    public function distinct(): self {

        $this -> distinct = true;
        return $this;
    }


    /**
     * Build the distinct condition
     * return void
     */
    private function buildDistinct(): void {

        if(true  === $this -> distinct) {
            $this -> query[] = 'DISTINCT';
        }
    }
}