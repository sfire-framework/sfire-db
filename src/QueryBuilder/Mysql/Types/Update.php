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
use sFire\Db\QueryBuilder\Mysql\Raw;


/**
 * Class Update
 * @package sFire\Db
 */
class Update extends TypeAbstract {


    use Table;
    use Joins;
    use Where;
    use OrderBy;
    use Limit;
    use Ignore;


    /**
     * Contains the values that needs to be updated in the database
     * @var array
     */
    private array $values = [];


    /**
     * Constructor
     * @param array $values
     */
    public function __construct(array $values) {
        $this -> values = $values;
    }


    /**
     * Build the query
     * @return Build
     */
    public function build(): Build {

        $this -> buildUpdate();
        $this -> buildJoins();
        $this -> buildValues();
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

        $this -> bind = array_merge($parameters, $this -> bind);
        return $this;
    }


    /**
     * Build the values
     * @return void
     */
    private function buildValues(): void {
        
        $this -> query[] = 'SET';

        $columns = [];

        foreach($this -> values as $column => $value) {

            if($value instanceof Select) {

                $build = $value -> build();
                $this -> bind = array_merge($build -> getParameters(), $this -> bind);
                $columns[] = sprintf('%s = (%s)', $column, $build -> getQuery());
            }
            elseif($value instanceof Raw) {
                $columns[] = sprintf('%s = %s', $column, $value -> __toString());
            }
            else {

                $columns[] = sprintf('%s = ?', $column);
                $this -> bind[] = $value;
            }
        }

        $this -> query[] = implode(', ', $columns);
    }


    /**
     * Build the select condition
     * return void
     */
    private function buildUpdate(): void {

        $this -> query[] = 'UPDATE';
        $this -> buildIgnore();
        $this -> buildTable();
    }
}