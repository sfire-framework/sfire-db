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
use sFire\Db\QueryBuilder\Mysql\TypeAbstract;


/**
 * Class Delete
 * @package sFire\Db
 */
class Delete extends TypeAbstract {


    use Table;
    use Joins;
    use Where;
    use OrderBy;
    use Limit;


    /**
     * Contains the table name of the delete statement
     * @var null|string
     */
    private ?string $delete = null;


    /**
     * Build the query
     * @return Build
     */
    public function build(): Build {

        $this -> buildDelete();
        $this -> buildJoins();
        $this -> buildWhere();
        $this -> buildOrderBy();
        $this -> buildLimit();
        $this -> replaceParamWithPositionBinding();
        $this -> replaceParamArrays();

        return new Build(implode(' ', $this -> query), $this -> getBind());
    }


    /**
     * Bind parameters to the query
     * @param array $parameters
     * @return self
     */
    public function bind(array $parameters) {

        $this -> bind = array_merge($this -> bind, $parameters);
        return $this;
    }


    /**
     * Build the select condition
     * return void
     */
    private function buildDelete(): void {

        $this -> query[] = 'DELETE FROM';
        $this -> buildTable();
    }
}