# sFire Db Mysqli Driver

- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Setup](#setup)
    - [Namespace](#namespace)
    - [Instance](#instance)
    - [Configuration](#configuration)
- [Usage](#usage)
    - [Creating a connection](#creating-a-connection)
    - [Preparing a query](#preparing-a-query)
    - [Fetching data](#fetching-data)
    - [Closing a connection](#closing-a-connection)
    - [Executing multiple queries](#executing-multiple-queries)
    - [Return number of rows](#return-number-of-rows)
    - [Get the last inserted id](#get-the-last-inserted-id)
    - [Manually escape data](#manually-escape-data)
    - [Debugging errors and performance](#debugging-errors-and-performance)
- [Examples](#examples)
- [Notes](#notes)



## Introduction

The sFire DB Mysqli Driver allows you to access MySQL database servers. This adapter lets you connect to MySQL databases and execute queries and statements. Selecting data to array's, objects, JSON or even iterators, execute update or delete statements, calling stored procedures and execute transactions are just a few examples.



## Requirements

- In order to have these functions available, you must compile PHP with support for the [mysqli extension](https://www.php.net/manual/en/book.mysqli.php).



## Installation

Install this package using [Composer](https://getcomposer.org/):
```shell script
composer require sfire-framework/sfire-db
```



## Setup

### Namespace
```php
use sFire\Db\Driver\Mysqli;
```



### Instance

```php
$connection = new Mysqli('127.0.0.1');
```



### Configuration

There are no configuration settings needed for this package.



## Usage

#### Creating a connection
Creating a database connection is a breeze. Provide a host (this can be a host name or IP address) with optional username and password and you are good to go. The adapter will automatically create a connection unless provided otherwise.

##### Syntax
```php
$connection = new Mysqli(string $host [,string $username = null] [, string $password = null] [, string $databaseName = null] [, int $port = 3306] [, bool $autoConnect = true] [, ?string $charset = null]);
```

##### Example 1: Connecting to a Mysql database
```php
$connection = new Mysqli('127.0.0.1');
```

##### Example 2: Connecting using a username, password and database table
```php
$connection = new Mysqli('127.0.0.1', 'username', 'password', 'MyDatabaseTable');
```

##### Example 3: Disable autoconnect
```php
$connection = new Mysqli('127.0.0.1', 'username', 'password', 'MyDatabaseTable', 3306, false); //Disable autoconnect
$connection -> connect(); //Manually connect to the database
```

##### Example 4: Force using specific charset/encoding
```php
$connection = new Mysqli('127.0.0.1', 'username', 'password', 'MyDatabaseTable', 3306, true, 'utf8');
```



#### Preparing a query

You can prepare SQL statements with the `query()` method. This method will not execute the query. To execute the query you can use the [execute()](#execute-prepared-query) method or the different kind of [fetch methods](#fetching-data) which are described below.

Parameters will be automatically be escaped. You can use the position bind variables or named variables.
```php
$connection -> query(string $query, array $parameters = []): self
```

##### Example 1: Preparing a query 
```php
$connection -> query('SELECT * FROM products WHERE id = 52');
```

##### Example 2: Preparing a query with position bind variables
```php
//Results in "SELECT * FROM products WHERE id = 52"
$connection -> query('SELECT * FROM products WHERE id = ?', [52]);
```

##### Example 3: Preparing a query with named variables
```php
//Results in "SELECT * FROM products WHERE id = 52"
$connection -> query('SELECT * FROM products WHERE id = :id', ['id' => 52]);
```

*Note: The `getQuery()` method will return the unparsed query while the `getParameters()` returns an `array` with the variables.*



#### Execute prepared query

To execute a statement/query you can use the `execute()` method. This method returns a boolean `true` for success or `false` for failure.

##### Example: 
```php
$success = $connection -> query('INSERT INTO customers (name) VALUES("John Smith")') -> execute();
var_dump($success); //Returns true/false
```



#### Fetching data

To retrieve data from the database you can use the `fetch()` method. This method returns an `array` (default) or JSON `string` with the selected data. You may also use the shortcut methods for data retrieval.

##### Syntax
```php
$connection -> fetch($type = self::ARRAY);
```

##### Example: Retrieve data using the fetch method
```php
$connection -> query('SELECT * FROM products');

$connection -> fetch(); //Returns an array
$connection -> fetch(Mysqli::ARRAY); //Equivalent of above
$connection -> fetch(Mysqli::ARRAY_OBJECT); //Returns an array with STD classes
$connection -> fetch(Mysqli::ARRAY_JSON); //Returns an array with JSON strings
$connection -> fetch(Mysqli::JSON); //Returns a JSON string
$connection -> fetch(Mysqli::ITERATOR); //Returns a ArrayIterator instance
```

##### Example 2: Retrieve data using shortcut methods
```php
$connection -> query('SELECT * FROM products');

$connection -> toArray(); //Returns an array
$connection -> toObjectArray(); //Returns an array with STD classes
$connection -> toJsonArray(); //Returns an array with STD classes
$connection -> toJson(); //Returns a JSON string
$connection -> toArrayIterator(); //Returns a ArrayIterator instance
```



#### Closing a connection

If a connection is created, you may close the connection manually by using the `close()` method.

##### Syntax
```php
$connection -> close();
```

##### Example: Closing the connection
```php
$connection = new Mysqli('127.0.0.1', 'username', 'password', 'MyDatabaseTable');
$connection -> query('INSERT INTO customers (name) VALUES("John Smith")') -> execute();
$connection -> close();
```



#### Executing multiple queries

The `multiQuery()` method will execute one or more queries and returns these as an `array`. For each statement that will be an array with the results of that statement in the parent array.

##### Syntax
```php
$connection -> multiQuery(string|array $query, array $parameters = []);
```

##### Example 1: Executing multiple queries as an array
```php
$connection -> multiQuery(['SELECT * FROM product', 'SELECT * FROM customer']);
```

##### Example 2: Executing multiple queries as string
```php
$connection -> multiQuery('SELECT * FROM product; SELECT * FROM customer');
```

Both will output similar to:
```
Array
(
    //Product results
    [0] => Array
        (
            [0] => Array
                (
                    [id] => 1
                    [title] => Product
                )
            [1] => Array
                ...
        )
    //Customer results    
    [1] => Array
        (
            [0] => Array
                (
                    [id] => 1
                    [name] => John Smith
                )
            [1] => Array
                ...
        )
)
```

##### Example 3: Executing multiple queries with variables
The multi query method also support binding position and named variables.
```php
$connection -> multiQuery('SELECT * FROM products WHERE id = ?; SELECT * FROM customers WHERE id = ?', [10, 52]);
```

```php
$connection -> multiQuery('SELECT * FROM products WHERE id = :productId; SELECT * FROM customers WHERE id = :customerId', ['productId' => 10, 'customerId' => 52]);
```



#### Return number of rows  

Fetching the number of rows of an executed query can be done by using the `getAmount()` method.

```php
$connection -> query('SELECT * FROM products') -> execute();
$amount = $connection -> getAmount();

echo $amount; //Output similar to "52"
```



#### Get the last inserted id

When you insert a new record into the database, you can retrieve the last inserted id with the getLastId method.

```php
$connection -> query('INSERT INTO customers (name) VALUES("John Smith")') -> execute();
$id = $connection -> getLastId();

echo $id; //Output similar to "25"
```



#### Get the number of affected rows

When you insert, delete or update record(s), you can retrieve the number of affected rows with the `getAffected()` method.

```php
$connection -> query('DELETE FROM products WHERE id > 100') -> execute();
$amount = $connection -> getAffected();

echo $amount; //Output similar to "25"
```



#### Manually escape data

sFire Db Mysqli Adapter will do the escaping of data for you, but if you want to manually escape data yourself, you can use the `escape()` method.

##### Syntax
```php
$connection -> escape(?string $data);
```

##### Example: Escaping data
```php
$id = $connection -> escape($_POST['id']);
$connection -> query('SELECT * FROM products WHERE id = '. $id);
```

*Note: Although this example will be safe to execute without any chance of an SQL injection attack, it is recommended to avoid escaping data manually and use the second parameter of the `query()` method to escape data has shown [here](#preparing-a-query).*



#### Debugging errors and performance
sFire Db Mysqli adapter comes with built-in debugging tools for measuring query times and debugging errors.

##### Error list
To retrieve a list of Mysql errors, you can use the `getErrorList()` method.
```php
$connection -> getErrorList();
```

This will output something similar to:
```
Array
(
    [0] => Array
        (
            [errno] => 1193
            [sqlstate] => HY000
            [error] => Unknown system variable 'a'
        )
)
```



##### Get last error

To retrieve a string description of the last error of Mysql, you can use the `getLastError()` method.
```php
$connection -> getLastError();
```

This will output something similar to:
```
Error message: Unknown system variable 'a'
```



##### Debugging query times

You can get statistics about the query times by using the `getTimings()` method.
```php
$connection -> getTimings();
```

This will output something similar to:
```
Array
(
    [parse] => 0.00012207031250000000
    [prepare] => 0.00248694419860839844
    [execute] => 0.00044298171997070312
    [store] => 0.00003004074096679688
    [fetch] => 0.00004792213439941406
)
```
###### Timing definition:
|Type|Definition|
|---|---|
|parse|Time to parse the query|
|prepare|Time to create the prepared statement|
|execute|Time to executing the statement|
|store|Time to store the result|
|fetch|Time to convert result to desired output|



## Examples

### Retrieve and display data
```php
$db = new Mysqli('127.0.0.1', 'username', 'password', 'tableName');
$products = $db -> query('SELECT * FROM products WHERE price > :price', ['price' => 10]) -> toArray();

if(is_array($product)) {
    foreach($products as $product) {
        print_r($product);
    }
}
```



## Notes

There are not notes for this package.