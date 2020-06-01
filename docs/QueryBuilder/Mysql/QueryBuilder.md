# sFire Db Mysql Querybuilder

- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Setup](#setup)sw
    - [Namespace](#namespace)
    - [Instance](#instance)
    - [Configuration](#configuration)
- [Usage](#usage)
    - [Setting a background image](setting-a-background-image)
- [Examples](#examples)
- [Notes](#notes)
    - [Chaining](#chaining)


## Introduction



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

#### Creating SELECT statement


##### Syntax
```php
$builder -> select($columns = []): Select;
```

##### Example 1:
```php
//SELECT * FROM products
$builder -> select() ->  table('products') -> build();
```

##### Example 2:
```php
//SELECT * FROM products WHERE id > 10
$builder -> select() ->  table('products') -> where('id > 10') -> build();
```

##### Example 3:
```php
//SELECT * FROM products WHERE id > 10
$builder -> select() ->  table('products') -> where('id > 10') -> orderBy('title') -> limit(10) -> build();
```