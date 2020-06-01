<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Db\Driver;

use ArrayIterator;
use mysqli as connection;
use mysqli_stmt;
use stdClass;
use sFire\Db\DbInterface;
use sFire\Db\Exception\InvalidArgumentException;
use sFire\Db\Exception\LogicException;
use sFire\Db\Exception\RuntimeException;


/**
 * Class Mysqli
 * @package sFire\Db
 */
class Mysqli implements DbInterface {


    private CONST ARRAY = 'array';
    private CONST ARRAY_OBJECT = 'array_object';
    private CONST ARRAY_JSON = 'array_json';
    private CONST JSON = 'json';
    private CONST ITERATOR = 'iterator';


	/**
     * Contains all the settings for connecting to the database
	 * @var stdClass
	 */
	private ?stdClass $data = null;


	/**
     * Contains the active connection
	 * @var connection
	 */
	private ?connection $connection = null;


    /**
     * @var mysqli_stmt
     */
    private ?mysqli_stmt $stmt = null;


    /**
     * Contains the parsed query
     * @var null|string
     */
    private ?string $query = null;


    /**
     * Contains the parsed bind parameters
     * @var null|array
     */
    private ?array $parameters;


    /**
     * Contains all the execution durations for debugging purposes
     * @var array
     */
    private array $timings = [

        'activation' => [],
        'times'      => [],
    ];


	/**
	 * Constructor
	 * @param string $host The host name or ip to connect to
	 * @param string $username The username of the database to connect to
	 * @param string $password The password of the database to connect to
	 * @param string $databaseName The database name
	 * @param int $port The port number to connect to
	 * @param string $charset The character set used for the connection
	 */
	public function __construct(string $host, string $username = null, string $password = null, string $databaseName = null, int $port = 3306, ?string $charset = null) {

        $this -> data = (object) $this -> data;
        $this -> data -> host 		    = $host;
        $this -> data -> username 	    = $username;
        $this -> data -> password 	    = $password;
        $this -> data -> databaseName 	= $databaseName;
        $this -> data -> port 		    = $port;
        $this -> data -> charset 	    = $charset;
	}


	/**
	 * Connect to Mysql server and set charset
     * @return void
	 */
	public function connect(): void {

		if(false === $this -> connection instanceof connection) {
			
			$this -> connection = new connection($this -> data -> host, $this -> data -> username, $this -> data -> password, $this -> data -> databaseName, $this -> data -> port);
			
			if($this -> data -> charset) {
	            $this -> connection -> set_charset($this -> data -> charset);
	        }
		}
	}


    /**
     * Prepare a raw query
     * @param string $query
     * @param array $parameters
     * @return self
     * @throws RuntimeException
     * @throws LogicException
     */
    public function query(string $query, array $parameters = []): self {

        $this -> connect();

        //Reset timings
        $this -> resetTime();

        //Parse the query
        $this -> addTime('parse');
        $bind = $this -> parse($parameters, $query);
        $this -> measureTime('parse');

        //Set the parsed query and parameters
        $this -> query = $query;
        $this -> parameters = $parameters;

        //Prepare statement
        $this -> addTime('prepare');
        $stmt = $this -> connection -> prepare($query);
        $this -> measureTime('prepare');

        if(false === $stmt) {
            throw new RuntimeException($this -> connection -> error);
        }

        if(count($bind) > 0) {

            if(false === ($stmt -> param_count == count($bind) - 1)) {
                throw new LogicException('Number of variable elements in query string does not match the number of bind variables');
            }

            call_user_func_array([$stmt, 'bind_param'], $bind);
        }

        $this -> stmt = $stmt;
        return $this;
    }


    /**
     * Execute a multi query
     * @param string|array $query
     * @param array $parameters
     * @return array
     * @throws InvalidArgumentException
     */
    public function multiQuery($query, $parameters = []): array {

        if(true === is_array($query)) {
            $query = implode(";\n", $query);
        }

        if(false === is_string($query)) {
            throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be of the type string or array, "%s" given', __METHOD__, gettype($query)));
        }

        $this -> connect();

        //Escape parameters
        $query = $this -> escapeSql($query, $parameters);
        $output = [];

        //Reset timings
        $this -> resetTime();

        //Execute multi query
        $this -> addTime('query');

        if($this -> connection -> multi_query($query)) {

            $this -> measureTime('query');
            $array = [];

            $this -> addTime('fetch');
            do {

                if($result = $this -> connection -> use_result()) {

                    while($row = $result -> fetch_array(MYSQLI_ASSOC)) {
                        $array[] = $row;
                    }

                    $result -> close();
                    $output[] = $array;
                    $array = [];
                }
            }

            while($this -> connection -> more_results() && $this -> connection -> next_result());
            $this -> measureTime('fetch');
        }

        return $output;
    }


    /**
     * Escapes string for statement usage
     * @param null|string|float|int $data
     * @return string
     * @throws InvalidArgumentException
     */
    public function escape(?string $data) {

        if(null === $data) {
            return null;
        }

        if(false === is_string($data) && false === is_numeric($data)) {
            throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be of the type string, float or int, "%s" given', __METHOD__, gettype($data)));
        }

        $this -> connect();

        return $this -> connection -> real_escape_string($data);
    }


    /**
     * Execute all statements and returns boolean on success/failure
     * @return bool
     * @throws RuntimeException
     */
    public function execute(): bool {

        $this -> addTime('execute');

        if(true !== $this -> stmt -> execute()) {

            if($this -> getLastErrno() > 0) {
                throw new RuntimeException(sprintf('A database error occurred with error number "%s" and message: "%s"', $this -> getLastErrno(), $this -> getLastError()));
            }

            $this -> measureTime('execute');
            return false;
        }

        $this -> measureTime('execute');
        return true;
    }


    /**
     * Returns results from statement as an result set
     * @param string $type
     * @return array|string
     */
    public function fetch($type = self::ARRAY) {

        //Execute statement
        $this -> addTime('execute');
        $this -> stmt -> execute();
        $this -> measureTime('execute');

        //Store result
        $this -> addTime('store');
        $result = $this -> stmt -> get_result();
        $this -> measureTime('store');

        $data = [];

        if(false !== $this -> stmt && 0 !== $this -> stmt -> field_count && 0 === $this -> stmt -> errno) {

            $this -> addTime('fetch');

            switch($type) {

                case self::JSON:
                case self::ITERATOR:
                case self::ARRAY:

                    while($row = $result -> fetch_array(1)) {
                        $data[] = $row;
                    }

                    break;

                case self::ARRAY_OBJECT:

                    while($row = $result -> fetch_object()) {
                        $data[] = $row;
                    }

                    break;

                case self::ARRAY_JSON:

                    while($row = $result -> fetch_array(1)) {
                        $data[] = json_encode($row, JSON_INVALID_UTF8_IGNORE);
                    }

                    break;
            }

            $this -> measureTime('fetch');
        }

        $this -> stmt -> close();
        unset($this -> stmt);

        if($type === self::JSON) {
            return json_encode($data, JSON_INVALID_UTF8_IGNORE);
        }

        if($type === self::ITERATOR) {
            return new ArrayIterator($data);
        }

        return $data;
    }


    /**
     * @return null|string
     */
    public function toJson(): ?string {
        return $this -> fetch(self::JSON);
    }


    /**
     * @return array
     */
    public function toArray(): ?array {
        return $this -> fetch(self::ARRAY);
    }


    /**
     * @return array
     */
    public function toObjectArray(): ?array {
        return $this -> fetch(self::ARRAY_OBJECT);
    }


    /**
     * @return array
     */
    public function toJsonArray(): ?array {
        return $this -> fetch(self::ARRAY_JSON);
    }


    /**
     * @return ArrayIterator
     */
    public function toArrayIterator(): ArrayIterator {
        return new ArrayIterator($this -> toArray());
    }


    /**
     * Returns the number of rows inserted
     * @return null|int
     */
    public function getAffectedRows(): ?int {
        return $this -> connection -> affected_rows ?? null;
    }


    /**
     * Returns the number of rows inserted
     * @return null|int
     */
    public function getLastInsertedId(): ?int {
        return $this -> connection -> insert_id ?? null;
    }


    /**
     * Returns the number of rows inserted
     * @return null|int
     */
    public function getAmount(): ?int {

        //Execute statement
        $this -> addTime('execute');
        $this -> stmt -> execute();
        $this -> measureTime('execute');

        $this -> addTime('store');
        $this -> stmt -> store_result();
        $this -> measureTime('store');

        return $this -> stmt -> num_rows ?? null;
    }


    /**
     * Returns the last error number
     * @return null|int
     */
    public function getLastErrno(): ?int {

        $this -> connect();
        return $this -> connection -> errno ?? null;
    }


    /**
     * Returns a list of errors from the last statement executed
     * @return null|array
     */
    public function getErrorList(): ?array {

        $this -> connect();
        return $this -> connection -> error_list ?? null;
    }


    /**
     * Returns the last error message
     * @return null|string
     */
    public function getLastError(): ?string {

        $this -> connect();
        return $this -> connection -> error ?? null;
    }


    /**
     * Closes the database connection
     * @return bool True if connection could be closed, false if not
     */
    public function close(): bool {

        if(false === $this -> connection instanceof connection) {
            return false;
        }

        $this -> connection -> close();
        return true;
    }


    /**
     * Returns all the execution durations for debugging purposes
     * @return array
     */
    public function getTimings(): array {
        return $this -> timings['times'];
    }


    /**
     * Returns the parsed query
     * @return null|string
     */
    public function getQuery(): ?string {
        return $this -> query;
    }


    /**
     * Returns the parsed variables
     * @return null|array
     */
    public function getVariables(): ?array {
        return $this -> parameters;
    }


    /**
     * Replaces single position binding character (?) with multiple position binding characters if associated bind variable is an array
     * @param string $query
     * @param array $parameters
     * @return void
     * @throws RuntimeException
     */
    protected function replaceParamArrays(string &$query, array &$parameters = []): void {

        $current = 0;
        $replaces = [];

        if(preg_match_all('/\?/', (string) $query, $params, PREG_OFFSET_CAPTURE) > 0) {

            foreach($params as $param) {

                foreach($param as $index => $match) {

                    if(false === array_key_exists($index + $current, $parameters)) {
                        throw new RuntimeException('Number of variable elements query string does not match number of bind variables');
                    }

                    if(true === is_array($parameters[$index + $current])) {

                        $replace    = implode(',', array_fill(0, count($parameters[$index + $current]), '?'));
                        $replaces[] = ['text' => $replace, 'position' => $match[1], 'bind' => $index + $current];
                    }
                }

                $current++;
            }
        }

        if(count($replaces) > 0) {

            $replaces = array_reverse($replaces);

            foreach($replaces as $replace) {

                $query = substr_replace($query, $replace['text'], $replace['position'], 1);
                $values = $parameters[$replace['bind']];

                unset($parameters[$replace['bind']]);
                array_splice($parameters, $replace['bind'], 0, array_values($values));
            }
        }
    }


    /**
     * Escape variables bind with a semicolon
     * @param string $query
     * @param array $params
     * @return string
     * @throws LogicException
     */
    private function escapeSemicolon(string $query, array $params): string {

        preg_match_all('#:((?:[a-zA-Z_])(?:[a-zA-Z0-9-_]+)?)#', (string) $query, $variables);

        if(true === isset($variables[0])) {

            foreach($variables[0] as $index => $variable) {

                if(false === array_key_exists($variables[1][$index], $params)) {
                    throw new LogicException(sprintf('Parameter "%s" missing from parameters', $params[1][$index]));
                }

                $var = $params[$variables[1][$index]];

                switch(gettype($var)) {

                    case 'string' : $var = '"' . $this -> escape($var) . '"'; break;
                    case 'NULL' : $var = 'NULL'; break;
                    default : $var = $this -> escape($var); break;
                }

                $query = str_replace($variables[0][$index], $var, $query);
            }
        }

        return $query;
    }


    /**
     * Escapes variables withing an sql query (replacing :var)
     * @param string $query
     * @param array $params
     * @return string
     */
    private function escapeSql(string $query, array $params = []) {

        //Check the type of param binding ("?" = all keys are numeric or ":" = all keys are strings)
        if(0 !== count(array_filter(array_keys($params), 'is_string'))) {
            return $this -> escapeSemicolon($query, $params);
        }

        //Variables are bind with the ? sign
        return $this -> escapeQuestionmark($query, $params);
    }


    /**
     * Escape bind variables with a question mark
     * @param string $query
     * @param array $params
     * @return string
     * @throws LogicException
     */
    private function escapeQuestionMark(string $query, array $params): string {

        preg_match_all('#([\\\]*)(\?)#', (string) $query, $variables);

        if(true === isset($variables[0])) {

            foreach($variables[0] as $index => $variable) {

                //Check if ? sign is escaped or not
                if(strlen($variables[1][$index]) % 2 !== 0) {
                    continue;
                }

                if(false === isset($params[$index])) {
                    throw new LogicException('Number of variable elements query string does not match number of bind variables');
                }

                $var = $params[$index];

                switch(gettype($var)) {

                    case 'string' : $var = '"' . $this -> escape($var) . '"'; break;
                    case 'NULL' : $var = 'NULL'; break;
                    default : $var = $this -> escape($var); break;
                }

                $query = preg_replace('#' . preg_quote($variable, '/') . '#', $var, $query, 1);
            }
        }

        return $query;
    }


    /**
     * Parse parameters and return it with types
     * @param array $params
     * @param string $query
     * @return array
     */
    private function parse(&$params, &$query) {

        $this -> replaceParamWithPositionBinding($query, $params);
        $this -> replaceParamArrays($query, $params);

        $bind = [];

        if(count($params) > 0) {

            $types = '';

            foreach($params as $param => $value) {

                switch(gettype($value)) {

                    case 'NULL'		:
                    case 'string'	: $types .= 's'; break;
                    case 'boolean'	:
                    case 'integer'	: $types .= 'i'; break;
                    case 'blob'		: $types .= 'b'; break;
                    case 'double'	: $types .= 'd'; break;
                }
            }

            $bind[] =& $types;

            foreach($params as $param => $value) {
                $bind[] =& $params[$param];
            }
        }

        return $bind;
    }


    /**
     * Replaces the param binding mode with the position binding mode. All param binding will be replaced with a position binding and the bind variable will be replaced with the new position binding variables
     * @param string $query
     * @param array $parameters
     * @return void
     * @throws RuntimeException
     */
    private function replaceParamWithPositionBinding(string &$query, array &$parameters = []): void {

        $replaces = [];
        $variables = [];

        if(preg_match_all('#:((?:[a-zA-Z_])(?:[a-zA-Z0-9-_]+)?)#', (string) $query, $params, PREG_OFFSET_CAPTURE) > 0) {

            foreach($params[1] as $index => $param) {

                if(false === isset($parameters[$param[0]])) {
                    throw new RuntimeException(sprintf('Parameter "%s" was not found in binding variables.', $param[0]));
                }

                $variables[] = $parameters[$param[0]];
                $replaces[]  = ['match' => $params[0][$index][0], 'position' => $params[0][$index][1]];
            }
        }

        if(count($replaces) > 0) {

            $replaces = array_reverse($replaces);

            foreach($replaces as $replace) {
                $query = substr_replace($query, '?', $replace['position'], strlen($replace['match']));
            }

            $parameters = $variables;
        }
    }


    /**
     * Add a new start time for a given type
     * @param string $type
     * @return void
     */
    private function addTime(string $type): void {
        $this -> timings['activation'][$type] = microtime(true);
    }


    /**
     * Add a new mes
     * @return void
     */
    private function resetTime(): void {

        $this -> timings = [

            'activation' => [],
            'times'      => [],
        ];
    }


    /**
     * Measure the amount of time that elapses between activation and deactivation based on a given type
     * @param string $type
     * @return void
     */
    private function measureTime(string $type): void {

        if(false === isset($this -> timings['activation'][$type])) {
            return;
        }

        $this -> timings['times'][$type] = number_format( microtime(true) - $this -> timings['activation'][$type], 20);
    }
}