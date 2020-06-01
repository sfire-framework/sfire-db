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
 * Class Table
 * @package sFire\Db
 */
trait Table {


    /**
     * Contains the table name to select table
     * @var null|string
     */
    private ?string $table = null;


    /**
     * Add a new table condition
     * @param string $tableName The name of the table to select table
     * @return self
     */
    public function table(string $tableName): self {

        $this -> table = $tableName;
        return $this;
    }


    /**
     * Build the table condition
     * return void
     */
    protected function buildTable(): void {

        if(null !== $this -> table) {
            $this -> query[] = $this -> table;
        }
    }
}