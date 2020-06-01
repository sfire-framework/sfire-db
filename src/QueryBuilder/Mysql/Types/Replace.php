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

use sFire\Db\QueryBuilder\Mysql\Build;


/**
 * Class Replace
 * @package sFire\Db
 */
class Replace extends Insert {


    /**
     * Build the query
     * @return Build
     */
    public function build(): Build {

        $this -> buildReplace();
        $this -> query[] = 'INTO';
        $this -> buildTable();
        $this -> buildColumns();
        $this -> buildValues();
        $this -> buildDuplicate();
        $this -> replaceParamWithPositionBinding();
        $this -> replaceParamArrays();

        return new Build(implode(' ', $this -> query), $this -> getBind());
    }


    /**
     * Build the insert condition
     * return void
     */
    private function buildReplace(): void {

        $this -> query[] = 'REPLACE';
        $this -> buildIgnore();
    }
}