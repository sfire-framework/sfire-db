<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Db\QueryBuilder\Mysql;

use sFire\Db\QueryBuilder\Mysql\Types\Call;
use sFire\Db\QueryBuilder\Mysql\Types\Delete;
use sFire\Db\QueryBuilder\Mysql\Types\Insert;
use sFire\Db\QueryBuilder\Mysql\Types\Replace;
use sFire\Db\QueryBuilder\Mysql\Types\Select;
use sFire\Db\QueryBuilder\Mysql\Types\Update;


/**
 * Class QueryBuilder
 * @package sFire\Db
 */
class QueryBuilder {


    /**
     * Add a select clause
     * @param mixed $columns
     * @return Select
     */
    public function select($columns = []): Select {
        return new Select($columns);
    }


    /**
     * Create an insert statement
     * @param array|Select $values
     * @return Insert
     */
    public function insert($values): Insert {
        return new Insert($values);
    }


    /**
     * Create an update statement
     * @param array $values
     * @return Update
     */
    public function update(array $values): Update {
        return new Update($values);
    }


    /**
     * Create an delete statement
     * @return Delete
     */
    public function delete(): Delete {
        return new Delete();
    }


    /**
     * Create an call statement
     * @param string $functionName
     * @return Call
     */
    public function call(string $functionName): Call {
        return new Call($functionName);
    }


    /**
     * Create a replace statement
     * @param array|Select $values
     * @return Replace
     */
    public function replace($values): Replace {
        return new Replace($values);
    }


    /**
     * Create a raw sql query
     * @param string $sql
     * @return Raw
     */
    public function raw(string $sql): Raw {
        return new Raw($sql);
    }
}