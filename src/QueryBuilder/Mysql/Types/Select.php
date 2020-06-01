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

use sFire\Db\Exception\BadMethodCallException;
use sFire\Db\QueryBuilder\Mysql\Build;
use sFire\Db\QueryBuilder\Mysql\TypeAbstract;


/**
 * Class Select
 * @package sFire\Db
 */
class Select extends TypeAbstract {

    use Table;
    use Where;
    use Having;
    use OrderBy;
    use GroupBy;
    use Limit;
    use Offset;
    use Joins;
    use Distinct;


    /**
     * Contains the columns of the select statement
     * @var array
     */
    public array $select = [];


    /**
     * Contains the query alias
     * @var null|string
     */
    private ?string $alias = null;


    /**
     * Constructor
     * @param mixed $columns
     * @throws BadMethodCallException
     */
    public function __construct($columns = []) {

        if(false === is_array($columns) && false === $columns instanceof Select) {
            throw new BadMethodCallException(sprintf('Argument 1 passed to %s should be an array or an instance of %s, "%s" given', __METHOD__, Select :: class, gettype($columns)));
        }

        if(true === $columns instanceof Select) {
            $columns = [$columns];
        }

        if(0 === count($columns)) {
            $columns = ['*'];
        }

        $this -> select = $columns;
    }


    /**
     * Adds a alias for the parsed query
     * @param string $alias
     * @return self
     */
    public function alias(string $alias): self {

        $this -> alias = $alias;
        return $this;
    }


    /**
     * @param null|string [optional] $type The type of union (all, distinct, distinctrow)
     * @return Union
     */
    public function union(string $type = null): Union {
        return new Union($this -> build(), $type);
    }


    /**
     * Build the query
     * @return Build
     */
    public function build(): Build {

        $this -> buildSelect();
        $this -> buildFrom();
        $this -> buildJoins();
        $this -> buildWhere();
        $this -> buildGroupBy();
        $this -> buildHaving();
        $this -> buildOrderBy();
        $this -> buildLimit();
        $this -> buildOffset();
        $this -> replaceParamWithPositionBinding();
        $this -> replaceParamArrays();

        $query = implode(' ', $this -> query);

        if(null !== $this -> alias) {
            $query = sprintf('(%s) AS %s', $query, $this -> alias);
        }

        return new Build($query, $this -> getBind());
    }


    /**
     * Bind parameters to the query
     * @param array $parameters
     * @return self
     */
    public function bind(array $parameters): self {

        $this -> bind = array_merge($this -> bind, $parameters);
        return $this;
    }


    /**
     * Build the select condition
     * return void
     */
    private function buildSelect(): void {

        $this -> query[] = 'SELECT';
        $this -> buildDistinct();

        if(0 === count($this -> select)) {
            $this -> query[] = '*';
        }
        else {

            foreach($this -> select as &$select) {

                if($select instanceof self) {

                    $sql = $select -> build();
                    $this -> bind($sql -> getParameters());
                    $select = $sql -> getQuery();
                }
            }

            $this -> query[] = implode(', ', $this -> select);
        }
    }


    /**
     * Build from and table condition
     * @return void
     */
    private function buildFrom(): void {

        if(null !== $this -> table) {

            $this -> query[] = 'FROM';
            $this -> buildTable();
        }
    }
}