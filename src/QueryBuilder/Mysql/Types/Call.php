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
 * Class Call
 * @package sFire\Db
 */
class Call extends TypeAbstract {


    /**
     * Contains the function name of the stored procedure
     * @var null|string
     */
    private ?string $call = null;


    /**
     * Constructor
     * @param string $functionName
     */
    public function __construct(string $functionName) {
        $this -> call = $functionName;
    }


    /**
     * Build the query
     * @return Build
     */
    public function build(): Build {

        $this -> buildCall();

        return new Build(implode(' ', $this -> query), $this -> parameters);
    }


    /**
     * Bind parameters to the query
     * @param array $parameters
     * @return self
     */
    public function parameters(array $parameters) {

        $this -> parameters = $parameters;
        return $this;
    }


    /**
     * Build the select condition
     * return void
     */
    private function buildCall(): void {

        $this -> query[] = 'CALL';
        $this -> buildParameters();
    }


    /**
     * Build the parameters
     * @return void
     */
    private function buildParameters(): void {
        $this -> query[] = sprintf('%s(%s)', $this -> call, implode(', ', array_fill(0, count($this -> parameters), '?')));
    }
}