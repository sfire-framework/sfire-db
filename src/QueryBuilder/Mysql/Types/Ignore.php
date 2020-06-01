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
 * Class Ignore
 * @package sFire\Db
 */
trait Ignore {


    /**
     * Contains if errors should be ignored
     * @var bool
     */
    private bool $ignore = false;


    /**
     * Sets if errors should be ignored
     * @param bool $ignoreErrors
     * @return self
     */
    public function ignore(bool $ignoreErrors = true): self {

        $this -> ignore = $ignoreErrors;
        return $this;
    }


    /**
     * Build the ignore condition
     * return void
     */
    protected function buildIgnore(): void {

        if(true  === $this -> ignore) {
            $this -> query[] = 'IGNORE';
        }
    }
}