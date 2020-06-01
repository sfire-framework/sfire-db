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

class Build {

    private array $parameters = [];

    private ?string $query = null;

    public function __construct($query, array $parameters = []) {

        $this -> query = $query;
        $this -> parameters = $parameters;
    }

    public function getParameters(): array {
        return $this -> parameters;
    }

    public function getQuery(): ?string {
        return $this -> query;
    }
}
