<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Db;

use sFire\Db\Exception\BadMethodCallException;


/**
 * Class AdapterTrait
 * @package sFire\Db
 */
trait AdapterTrait {


    /**
     * Contains a driver instance
     * @var null|DbInterface
     */
    private ?DbInterface $adapter = null;


    /**
     * Returns the driver
     * @return DbInterface
     * @throws BadMethodCallException
     */
    public function getAdapter(): DbInterface {

        if(null === $this -> adapter) {
            throw new BadMethodCallException(sprintf('No database adapter was set in "%s"', __METHOD__));
        }

        return $this -> adapter;
    }


    /**
     * @param DbInterface $adapter
     * @return void
     */
    public function setAdapter(DbInterface $adapter): void {
        $this -> adapter = $adapter;
    }
}