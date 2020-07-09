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

use sFire\Db\Exception\RuntimeException;


/**
 * Class TypeAbstract
 * @package sFire\Db
 */
abstract class TypeAbstract {


    /**
     * Contains the parts of the query
     * @var array
     */
    public array $query = [];


    /**
     * Contains the parameters that needs to be escaped
     * @var array
     */
    protected array $parameters = [];


    /**
     * Contains the bind parameters that needs to be escaped
     * @var array
     */
    protected array $bind = [];


    /**
     * Contains an instance of Build
     * @var null|Build
     */
    private ?Build $build = null;


    /**
     * Build the query
     * @return Build
     */
    abstract public function build(): Build;


    /**
     * Build the query and returns the query string without the parameters
     * @return string
     */
    public function __toString(): string {
        return $this -> build() -> getQuery();
    }


    /**
     * Returns the sql statement as a string
     * @return string
     */
    public function getQuery(): string {

        if(null === $this -> build) {
            $this -> build = $this -> build();
        }

        return $this -> build -> getQuery();
    }


    /**
     * Returns the bind parameters
     * @return array
     */
    public function getParameters(): array {

        if(null === $this -> build) {
            $this -> build = $this -> build();
        }

        return $this -> build -> getParameters();
    }


    /**
     * Replaces the param binding mode with the position binding mode. All param binding will be replaced with a position binding and the bind variable will be replaced with the new position binding variables
     * @throws RuntimeException
     * @return void
     */
    protected function replaceParamWithPositionBinding(): void {

        $replaces  = [];
        $variables = [];

        foreach($this -> query as $i => $query) {

            if(preg_match_all('#:((?:[a-zA-Z_])(?:[a-zA-Z0-9-_]+)?)#', (string) $query, $params, PREG_OFFSET_CAPTURE) > 0) {

                foreach($params[1] as $index => $param) {

                    if(false === isset($this -> bind[$param[0]])) {
                        throw new RuntimeException(sprintf('Parameter "%s" was not found in binding variables.', $param[0]));
                    }

                    $variables[] = $this -> bind[$param[0]];
                    $replaces[]  = ['match' => $params[0][$index][0], 'position' => $params[0][$index][1], 'query' => $i];
                }
            }
        }
        
        if(count($replaces) > 0) {

            $replaces = array_reverse($replaces);

            foreach($replaces as $replace) {
                $this -> query[$replace['query']] = substr_replace($this -> query[$replace['query']], '?', $replace['position'], strlen($replace['match']));
            }

            $bind = array_merge($this -> bind, $variables);

            foreach($bind as $key => $value) {

                if(false === is_int($key)) {
                    unset($bind[$key]);
                }
            }

            $this -> bind = $bind;
        }
    }


    /**
     * Replaces single position binding character (?) with multiple position binding characters if associated bind variable is an array
     * @throws RuntimeException
     * @return void
     */
    protected function replaceParamArrays(): void {

        $current = 0;
        $replaces = [];

        foreach($this -> query as $i => $query) {

            if(preg_match_all('/\?/', (string) $query, $params, PREG_OFFSET_CAPTURE) > 0) {

                foreach($params as $param) {
                
                    foreach($param as $index => $match) {

                        if(false === array_key_exists($index + $current, $this -> bind)) {
                            throw new RuntimeException('Number of variable elements query string does not match number of bind variables');
                        }

                        if(true === is_array($this -> bind[$index + $current])) {

                            $replace    = implode(',', array_fill(0, count($this -> bind[$index + $current]), '?'));
                            $replaces[] = ['text' => $replace, 'position' => $match[1], 'bind' => $index + $current, 'query' => $i];
                        }
                    }

                    $current++;
                }
            }
        }

        if(count($replaces) > 0) {

            $replaces = array_reverse($replaces);

            foreach($replaces as $replace) {

                $this -> query[$replace['query']] = substr_replace($this -> query[$replace['query']], $replace['text'], $replace['position'], 1);
                $values = $this -> bind[$replace['bind']];

                unset($this -> bind[$replace['bind']]);

                array_splice($this -> bind, $replace['bind'], 0, array_values($values));
            }
        }
    }


    /**
     * Merge bind and parameters and returns the combined array
     * @return array
     */
    protected function getBind(): array {
        return array_merge(array_values($this -> bind), array_values($this -> parameters));
    }
}