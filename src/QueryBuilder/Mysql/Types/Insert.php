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
use sFire\Db\QueryBuilder\Mysql\Raw;


/**
 * Class Insert
 * @package sFire\Db
 */
class Insert extends TypeAbstract {


    use Table;
    use Ignore;


    /**
     * Contains the values that needs to be inserted into the database
     * @var array|Select
     */
    private $values = null;


    /**
     * Contains the columns when inserting a select statement
     * @var array
     */
    private array $columns = [];


    /**
     * Contains if the statement should use a on duplicate key statement
     * @var bool
     */
    private bool $duplicate = false;


    /**
     * Contains the values for the on duplicate key statement
     * @var array
     */
    private array $duplicateValues = [];


    /**
     * Constructor
     * @param array|Select $values
     * @throws BadMethodCallException
     */
    public function __construct($values) {

        if(false === is_array($values) && false === $values instanceof Select) {
            throw new BadMethodCallException(sprintf('Parameters 1 given to %s() should be an array or an instance of %s, "%s" given.', __METHOD__, Select :: class, gettype($values)));
        }

        $this -> values = $values;
    }


    /**
     * Build the query
     * @return Build
     */
    public function build(): Build {

        $this -> buildInsert();
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
     * Create a in duplicate key update statement. If no columns/values are given, the columns will be used from the "values" method
     * @param array $columns A key value array (column and value)
     * @return self
     */
    public function duplicate(array $columns = []): self {

        $this -> duplicateValues = $columns;
        $this -> duplicate = true;

        return $this;
    }


    /**
     * Add the column names when inserting a sFire select statement
     * @param array $columns
     * @return self
     * @throws BadMethodCallException
     */
    public function columns(array $columns): self {

        if(false === $this -> isSequentialArray($columns) || true === $this -> isMultidimensionalArray($columns)) {
            throw new BadMethodCallException(sprintf('Argument 1 passed to %s should be a sequential array', __METHOD__));
        }

        $this -> columns = $columns;
        return $this;
    }


    /**
     * Build the insert condition
     * return void
     */
    private function buildInsert(): void {

        $this -> query[] = 'INSERT';
        $this -> buildIgnore();
    }


    /**
     * Build the on duplicate key condition
     * @return void
     */
    protected function buildDuplicate(): void {

        if(true === $this -> duplicate) {

            $this -> query[] = 'ON DUPLICATE KEY UPDATE';
            $columns = $this -> getColumnsNames();

            if(count($this -> duplicateValues) === 0) {
                $this -> query[] = implode(', ', array_map(fn($column) => $column = sprintf('%s = VALUES(%1$s)', $column), $columns));
            }
            else {

                $updates = [];

                foreach($columns as $column) {

                    if(false === isset($this -> duplicateValues[$column])) {
                        $this -> duplicateValues[] = $column;
                    }
                }

                foreach($this -> duplicateValues as $column => $value) {

                    if($value instanceof Raw) {
                        $updates[] = sprintf('%s = %s', $column, $value -> __toString());
                    }
                    else {

                        if(true === is_int($column)) {
                            $updates[] = sprintf('%s = VALUES(%1$s)', $value);
                        }
                        else {

                            $updates[] = sprintf('%s = ?', $column);
                            $this -> bind[] = $value;
                        }
                    }
                }

                $this -> query[] = implode(', ', $updates);
            }
        }
    }


    /**
     * Build the values as a query
     * @return void
     */
    protected function buildValues(): void {

        if($this -> values instanceof Select) {

            $build = $this -> values -> build();
            $this -> query[] = $build -> getQuery();
            $this -> bind = array_merge($this -> bind, $build -> getParameters());
        }
        elseif(false === $this -> isMultidimensionalArray($this -> values) && false === $this -> isSequentialArray($this -> values)) {

            $this -> query[] = 'VALUES';
            $tmp = [];

            foreach($this -> values as $value) {

                if($value instanceof Select) {

                    $build = $value -> build();
                    $this -> bind = array_merge($build -> getParameters(), $this -> bind);
                    $tmp[] = sprintf('(%s)', $build -> getQuery());
                }
                elseif($value instanceof Raw) {
                    $tmp[] = $value -> __toString();
                }
                else {

                    $this -> bind[] = $value;
                    $tmp[] = '?';
                }
            }

            $this -> query[] = sprintf('(%s)', implode(', ', $tmp));
        }
        elseif(true === $this -> isMultidimensionalArray($this -> values)) {

            $this -> query[] = 'VALUES';
            $sets = [];

            foreach($this -> values as $values) {

                $tmp = [];

                foreach($values as $value) {

                    if($value instanceof Raw) {
                        $tmp[] = $value -> __toString();
                    }
                    else {

                        $this -> bind[] = $value;
                        $tmp[] = '?';
                    }
                }

                $sets[] = sprintf('(%s)', implode(', ', $tmp));
            }

            $this -> query[] = implode(', ', $sets);
        }
    }



    /**
     * Build the columns condition
     * @return void
     */
    protected function buildColumns(): void {

        $columns = $this -> getColumnsNames();
        $this -> query[] = sprintf('(%s)', implode(', ', $columns));
    }


    /**
     * Returns if an array is multidimensional
     * @param array $array
     * @return bool
     */
    private function isMultidimensionalArray(array $array): bool {
        return (count(array_filter($array, 'is_array')) > 0);
    }


    /**
     * Returns if an array is associative or sequential
     * @param array $array
     * @return bool
     */
    private function isSequentialArray(array $array): bool {

        if(array() === $array) {
            return false;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }


    /**
     * Retrieve the column names as an array
     * @return array
     * @throws BadMethodCallException
     */
    private function getColumnsNames(): array {

        if($this -> values instanceof Select) {
            
            if(count($this -> values -> select) > 0) {

                if(true === in_array('*', $this -> values -> select)) {
                    throw new BadMethodCallException('Cannot use a wildcard "*" as column name. Please define the column names manually.');
                }
            }

            if(count($this -> columns) > 0) {
                return $this -> columns;
            }

            return $this -> values -> select;
        }

        if(false === $this -> isMultidimensionalArray($this -> values) && false === $this -> isSequentialArray($this -> values)) {
            return array_keys($this -> values);
        }

        if(true === $this -> isMultidimensionalArray($this -> values)) {
            return array_keys($this -> values[0] ?? []);
        }

        return $this -> values;
    }
}