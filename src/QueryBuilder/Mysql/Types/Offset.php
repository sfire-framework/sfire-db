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
 * Class Offset
 * @package sFire\Db
 */
trait Offset {


    /**
     * Contains all the offset conditions
     * @var int
     */
    private ?int $offset = null;


    /**
     * Add a new offset condition
     * @param int $start
     * @return self
     */
    public function offset(int $start): self {

        $this -> offset = $start;
        return $this;
    }


    /**
     * Build the offset condition
     * return void
     */
    private function buildOffset(): void {

        if(true === isset($this -> limit) && null !== $this -> limit) {

            if(null !== $this -> offset) {
                $this -> query[] = sprintf(',%s', $this -> offset);
            }
        }
    }
}