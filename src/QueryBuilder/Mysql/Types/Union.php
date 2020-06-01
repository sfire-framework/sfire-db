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
use stdClass;


/**
 * Class Union
 * @package sFire\Db
 */
class Union {


    /**
     * Contains the build from the previous select statement
     * @var null|Build
     */
    protected ?Build $sqlBuild = null;


    /**
     * Contains the union type (all, distinct, distinctrow)
     * @var null|string
     */
    protected ?string $unionType = null;


    /**
     * Constructor
     * @param Build $sqlBuild
     * @param null|string $unionType The type of union (all, distinct, distinctrow)
     */
    public function __construct(Build $sqlBuild, string $unionType = null) {

        $this -> unionType = $unionType ? strtoupper($unionType) : null;
        $this -> sqlBuild  = $sqlBuild;
    }


    /**
     * Add a select clause
     * @param mixed $columns
     * @return Select
     */
    public function select($columns = []): Select {

        $select = $this -> getSelectInstance($columns);
        $select -> query[] = $this -> sqlBuild -> getQuery();
        $select -> query[] = 'UNION';

        if(null !== $this -> unionType) {
            $select -> query[] = $this -> unionType;
        }

        $select -> bind($this -> sqlBuild -> getParameters());

        return $select;
    }


    /**
     * Return a new instance of a Select
     * @param mixed $parameters
     * @return Select
     */
    protected function getSelectInstance($parameters): Select {
        return new Select($parameters);
    }
}