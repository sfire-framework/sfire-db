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


/**
 * Class Raw
 * @package sFire\Db
 */
class Raw {


    /**
     * Contains the raw sql
     * @var null|string
     */
    private ?string $sql = null;


    /**
     * Constructor
     * @param string $sql The raw sql string
     */
    public function __construct(string $sql) {
        $this -> sql = $sql;
    }


    /**
     * Returns the raw sql string
     * @return null|string
     */
    public function __toString(): ?string {
        return $this -> sql;
    }
}