# sFire Db Mysql Querybuilder Select

- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Setup](#setup)
    - [Namespace](#namespace)
    - [Instance](#instance)
    - [Configuration](#configuration)
- [Usage](#usage)
    - [Selecting columns from table](#selecting-columns-from-table)
    - [Where condition](#where-condition)
    - [Binding variables](#binding-variables)
    - [Distinct](#distinct)
    - [Left join](#left-join)
    - [Right join](#right-join)
    - [Inner join](#inner-join)
    - [Cross join](#cross-join)
    - [Unions](#unions)
    - [Having](#having)
    - [Order by](#order-by)
    - [Limit and offset](#limit-and-offset)
- [Examples](#examples)
- [Notes](#notes)
    - [Chaining](#chaining)


## Introduction
The sFire Db Mysql Querybuilder Select provides a convenient, fluent interface to creating and running Mysql select queries. It will not push you to be too specific and creating difficult to understand objects (and therefore queries) which gives the advantages of being generic while building the query. 

It uses Mysqli (position variable binding) and PDO (named variable binding) parameter binding to protect your application against SQL injection attacks.



## Requirements

There are no requirements needed for this package.



## Installation

Install this package using [Composer](https://getcomposer.org/):
```shell script
composer require sfire-framework/sfire-db
```



## Setup

### Namespace
```php
use sFire\Db\QueryBuilder\Mysql\QueryBuilder;
```



### Instance

```php
$builder = new QueryBuilder();
```



### Configuration

There are no configuration settings needed for this package.



## Usage

### Selecting columns from table
To select data from a table, you can use the `select()` and `table()` methods.

#### Example 1: Selecting all columns
```php
$builder -> select() -> table('products') -> getQuery();
```
Results in:
```sql
SELECT * FROM products
```

#### Example 2: Selecting specific columns
```php
$builder -> select(['id', 'title']) -> table('products') -> getQuery();
```
Results in:
```sql
SELECT id, title FROM products
```

#### Example 3: Selecting custom column
```php
$builder -> select(['"Product 1" as title']) -> getQuery();
```
Results in:
```sql
SELECT "Product 1" as title
```



### Where condition

To select data from a table, you can use the `where()` method. In combination with the `bind()` method, you can add variables that will be used to create a prepared statement for safe SQL execution.

#### Example 1: Adding simple WHERE statement
```php
$builder -> select() -> table('products') -> where('id = 1 AND title = "Product 1"') -> getQuery();
```
Or:
```php
$builder -> select() -> table('products') -> where('id = 1', 'title = "Product 1"') -> getQuery();
```
Or:
```php
$builder -> select() -> table('products') -> where('id = 1') -> where('title = "Product 1"') -> getQuery();
```
Results in:
```sql
SELECT * FROM products WHERE id = 1 AND title = "Product 1"
```



### Binding variables

To safely execute queries with variables preventing SQL injection attakcts, you can add variables with the `bind()` method. sFire will create a prepared statement to execute the query safely. The bind method accepts an array with values or if you want to use named variables, a key/value array.

#### Example 1: Position variables binding
```php
$builder -> select() -> table('products') -> where('id = ?') -> bind([10]) -> getQuery();
$builder -> select() -> table('products') -> where('title = ? OR title = ?') -> bind(['foo', 'bar']) -> getQuery();
```

sFire makes it easy to add an array with id's to put into a `where in()` condition:
```php
$builder -> select() -> table('products') -> where('id IN(?)') -> bind([[10, 20, 30, 40]]) -> getQuery();
```

#### Example 2: Named variables binding
You can also use named variable binding where you can name a variable and use this unique name in your query:
```php
$builder -> select() -> table('products') -> where('id = :id') -> bind(['id' => 10]) -> getQuery();
$builder -> select() -> table('products') -> where('sku = :code OR ean = :code') -> bind(['code' => '123456789']) -> getQuery();
```

The `where in()` condition also works with named variables:
```php
$builder -> select() -> table('products') -> where('id IN(:ids)') -> bind(['ids' => [10, 20, 30, 40]]) -> getQuery();
```

You may also retrieve the parameters by calling the `getParameters()` method. This method only retrieves the used parameters:
```php
$builder -> select() -> table('products') -> where('id = ?') -> bind([10]) -> getParameters(); //Output: Array(10)
$builder -> select() -> table('products') -> where('id = ? OR id = ? OR id = ?') -> bind([10, 20, 30]) -> getParameters(); //Output: Array(10, 20, 30)
$builder -> select() -> table('products') -> where('id = ?') -> bind([10, 20, 30]) -> getParameters(); //Output: Array(10)
```



### Distinct

The `distinct()` method is used to return only distinct (different) values. Inside a table, a column often contains many duplicate values; and sometimes you only want to list the different (distinct) values.

#### Example: Using distinct
```php
$builder -> select(['price']) -> distinct() -> table('products') -> getQuery();
```
Results in:
```sql
SELECT DISTINCT price FROM products
```



### Left join / right join

If you would like to perform a "left join" or "right join", you can use the `leftJoin()` or `rightJoin()` methods. These methods have the same signature as the join method:

#### Example: Using left join
```php
$builder -> select() -> table('products') -> leftJoin() -> getQuery();
```
Results in:
```sql
SELECT DISTINCT price FROM products
```



### Having

The `having()` method is used to specify filter conditions for a group of rows or aggregates.

#### Example: Using having
```php
$builder -> select(['SUM(price) as amount']) -> table('products') -> where('price > 100') -> having('amount > 1000') -> getQuery();
```
Results in:
```sql
SELECT SUM(price) as amount FROM products WHERE price > 100 HAVING amount > 1000
```



### Order by

The `orderBy()` method is used to sort the result-set in ascending or descending order.

#### Example: Using order by
```php
$builder -> select() -> table('products') -> orderBy('title DESC') -> getQuery();
```
Results in:
```sql
SELECT * FROM products ORDER BY title DESC
```



### Limit and offset

The `limit()` and `offset()` methods are used to specify the number of records to return and from where to start.

#### Example 1: Using limit
```php
$builder -> select() -> table('products') -> limit(10) -> getQuery();
```
Results in:
```sql
SELECT * FROM products LIMIT 10
```

#### Example 2: Using limit in combination with offset
```php
$builder -> select() -> table('products') -> limit(10) -> offset(20) -> getQuery();
```
Results in:
```sql
SELECT * FROM products LIMIT 10,20
```