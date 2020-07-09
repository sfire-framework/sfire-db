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

use ArrayIterator;


/**
 * Interface DbInterface
 * @package sFire\Db
 */
interface DbInterface {


    /**
     * Constructor
     * @param string $host The host name or ip to connect to
     * @param string $username [optional] The username of the database to connect to
     * @param string $password [optional] The password of the database to connect to
     * @param string $databaseName [optional] The database name
     * @param int $port [optional] The port number to connect to
     * @param string $charset [optional] The character set used for the connection
     */
    public function __construct(string $host, string $username = null, string $password = null, string $databaseName = null, int $port = 3306, ?string $charset = null);


    /**
     * Connect to server
     */
    public function connect();


    /**
     * Closes the connection
     */
    public function close();


    /**
     * Execute all statements and returns boolean on success/failure
     * @return bool
     */
    public function execute(): bool;


    /**
     * Convert results to one JSON string
     * @return null|string
     */
    public function toJson(): ?string;


    /**
     * Convert result to an array
     * @return null|array
     */
    public function toArray(): ?array;


    /**
     * Convert results to an array with stdClasses
     * @return null|array
     */
    public function toObjectArray(): ?array;


    /**
     * Convert results to an array with JSON strings
     * @return null|array
     */
    public function toJsonArray(): ?array;


    /**
     * Convert results to an ArrayIterator
     * @return null|ArrayIterator
     */
    public function toArrayIterator(): ?ArrayIterator;


    /**
     * Prepare a raw query
     * @param string $query The raw query
     * @param array $params Parameters to escape
     * @return self
     */
    public function query(string $query, array $params = []): self;
}