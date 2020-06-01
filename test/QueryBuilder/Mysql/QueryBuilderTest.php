<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use sFire\Db\QueryBuilder\Mysql\QueryBuilder;


/**
 * Class QueryBuilderTest
 */
final class QueryBuilderTest extends TestCase {


    /**
     * Contains instance of QueryBuilder
     * @var null|QueryBuilder
     */
    private ?QueryBuilder $builder = null;


    /**
     * Setup. Created new QueryBuilder instance
     * @return void
     */
    protected function setUp(): void {
        $this -> builder = new QueryBuilder();
    }


    /**
     * Test select
     * @return void
     */
    public function testSelect(): void {

        //Simple select #1
        $sql = $this -> builder -> select() -> table('product') -> build();
        $this -> assertEquals('SELECT * FROM product', $sql -> getQuery());

        //More advanced select query #2
        $sql = $this -> builder ->
                    select(['id', 'title']) ->
                    distinct() ->
                    table('product') ->
                    where('id > 0') ->
                    where('id > 10') ->
                    having('id > 0') ->
                    orderBy('id', 'DESC') ->
                    limit(10) ->
                    offset(5) ->
                    build();

        $this -> assertEquals('SELECT DISTINCT id, title FROM product WHERE (id > 0 AND id > 10) HAVING (id > 0) ORDER BY id DESC LIMIT 10 ,5', $sql -> getQuery());

        //Select in select with alias
        $sql = $this -> builder -> select($this -> builder -> select(['COUNT(*)']) -> table('product') -> alias('amount')) -> table('product') -> build();
        $this -> assertEquals('SELECT (SELECT COUNT(*) FROM product) AS amount FROM product', $sql -> getQuery());
    }


    /**
     * Test retrieving the query method without the build method
     * @return void
     */
    public function testGetQuery() {

        //Test select
        $this -> assertEquals('SELECT * FROM product', $this -> builder -> select() -> table('product') -> getQuery());

        // Test union
        $this -> assertEquals('SELECT * FROM product UNION SELECT * FROM product', $this -> builder -> select() -> table('product') -> union() -> select() -> table('product') -> getQuery());

        //Test insert
        $this -> assertEquals('INSERT IGNORE INTO product (title) VALUES (?)', $this -> builder -> insert(['title' => 'test']) -> table('product') -> ignore() -> getQuery());

        //Test replace
        $this -> assertEquals('REPLACE IGNORE INTO product (title) VALUES (?)', $this -> builder -> replace(['title' => 'test']) -> table('product') -> ignore() -> getQuery());

        //Test delete
        $this -> assertEquals('DELETE FROM product WHERE (id > 0) ORDER BY id DESC LIMIT 10', $this -> builder -> delete() -> table('product') -> where('id > 0') -> orderBy('id', 'desc') -> limit(10) -> getQuery());

        //Test join
        $this -> assertEquals('SELECT * FROM product LEFT JOIN product INNER JOIN product RIGHT JOIN product CROSS JOIN product', $this -> builder -> select() -> table('product') -> leftJoin('product') -> innerJoin('product') -> rightJoin('product') -> crossJoin('product') -> getQuery());

        //Test update
        $this -> assertEquals('UPDATE product SET title = ?', $this -> builder -> update(['title' => 'test']) -> table('product') -> getQuery());

        //Test call
        $this -> assertEquals('CALL test()', $this -> builder -> call('test') -> getQuery());
    }


    /**
     * Test retrieving the parameters method without the build method
     * @return void
     */
    public function testGetParameters() {

        //Test select
        $this -> assertEquals([1], $this -> builder -> select() -> table('product') -> where('id = ?') -> bind([1]) -> getParameters());

        // Test union
        $this -> assertEquals([1], $this -> builder -> select() -> table('product') -> union() -> select() -> table('product') -> where('id = ?') -> bind([1]) -> getParameters());

        //Test insert
        $this -> assertEquals(['test'], $this -> builder -> insert(['title' => 'test']) -> table('product') -> ignore() -> getParameters());

        //Test replace
        $this -> assertEquals(['test'], $this -> builder -> replace(['title' => 'test']) -> table('product') -> ignore() -> getParameters());

        //Test delete
        $this -> assertEquals([1], $this -> builder -> delete() -> table('product') -> where('id = ?') -> bind([1]) -> orderBy('id', 'desc') -> limit(10) -> getParameters());

        //Test join
        $this -> assertEquals([1], $this -> builder -> select() -> table('product') -> where('id = ?') -> bind([1]) -> leftJoin('product') -> innerJoin('product') -> rightJoin('product') -> crossJoin('product') -> getParameters());

        //Test update
        $this -> assertEquals(['test'], $this -> builder -> update(['title' => 'test']) -> table('product') -> getParameters());

        //Test call
        $this -> assertEquals([1], $this -> builder -> call('test') -> parameters([1]) -> getParameters());
    }


    /**
     * Test union
     * @return void
     */
    public function testUnion(): void {

        $sql = $this -> builder -> select() -> table('product') -> union() -> select() -> table('product') -> build();
        $this -> assertEquals('SELECT * FROM product UNION SELECT * FROM product', $sql -> getQuery());

        $sql = $this -> builder -> select() -> table('product') -> union('all') -> select() -> table('product') -> build();
        $this -> assertEquals('SELECT * FROM product UNION ALL SELECT * FROM product', $sql -> getQuery());
    }


    /**
     * Test insert
     * @return void
     */
    public function testInsert(): void {

        //Simple insert statement with parameter binding
        $sql = $this -> builder -> insert(['title' => 'test']) -> table('product') -> ignore() -> build();
        $this -> assertEquals('INSERT IGNORE INTO product (title) VALUES (?)', $sql -> getQuery());
        $this -> assertEquals(['test'], $sql -> getParameters());

        //Insert statement with raw query
        $sql = $this -> builder -> insert(['title' => $this -> builder -> raw('"test"')]) -> table('product') -> build();
        $this -> assertEquals('INSERT INTO product (title) VALUES ("test")', $sql -> getQuery());
        $this -> assertEquals([], $sql -> getParameters());

        //Insert from select statement #1
        $sql = $this -> builder -> insert($this -> builder -> select(['id', 'title AS test']) -> table('product')) -> columns(['id', 'test']) -> table('product') -> build();
        $this -> assertEquals('INSERT INTO product (id, test) SELECT id, title AS test FROM product', $sql -> getQuery());
        $this -> assertEquals([], $sql -> getParameters());

        //Insert from select statement #2
        $sql = $this -> builder -> insert(['title' => $this -> builder -> select(['title AS test']) -> table('product')]) -> table('product') -> build();
        $this -> assertEquals('INSERT INTO product (title) VALUES ((SELECT title AS test FROM product))', $sql -> getQuery());
        $this -> assertEquals([], $sql -> getParameters());

        //Insert statement with on duplicate key #1
        $sql = $this -> builder -> insert(['title' => 'test']) -> table('product') -> duplicate() -> build();
        $this -> assertEquals('INSERT INTO product (title) VALUES (?) ON DUPLICATE KEY UPDATE title = VALUES(title)', $sql -> getQuery());
        $this -> assertEquals(['test'], $sql -> getParameters());

        //Insert statement with on duplicate key #2
        $sql = $this -> builder -> insert(['title' => 'test']) -> table('product') -> duplicate(['id' => '10']) -> build();
        $this -> assertEquals('INSERT INTO product (title) VALUES (?) ON DUPLICATE KEY UPDATE id = ?, title = VALUES(title)', $sql -> getQuery());
        $this -> assertEquals(['test', '10'], $sql -> getParameters());
    }


    /**
     * Test replace
     * @return void
     */
    public function testReplace(): void {

        //Simple insert statement with parameter binding
        $sql = $this -> builder -> replace(['title' => 'test']) -> table('product') -> ignore() -> build();
        $this -> assertEquals('REPLACE IGNORE INTO product (title) VALUES (?)', $sql -> getQuery());
        $this -> assertEquals(['test'], $sql -> getParameters());

        //Insert statement with raw query
        $sql = $this -> builder -> replace(['title' => $this -> builder -> raw('"test"')]) -> table('product') -> build();
        $this -> assertEquals('REPLACE INTO product (title) VALUES ("test")', $sql -> getQuery());
        $this -> assertEquals([], $sql -> getParameters());

        //Insert from select statement #1
        $sql = $this -> builder -> replace($this -> builder -> select(['id', 'title AS test']) -> table('product')) -> columns(['id', 'test']) -> table('product') -> build();
        $this -> assertEquals('REPLACE INTO product (id, test) SELECT id, title AS test FROM product', $sql -> getQuery());
        $this -> assertEquals([], $sql -> getParameters());


        $sql = $this -> builder -> replace(['title' => $this -> builder -> select(['title AS test']) -> table('product')]) -> table('product') -> build();
        $this -> assertEquals('REPLACE INTO product (title) VALUES ((SELECT title AS test FROM product))', $sql -> getQuery());
        $this -> assertEquals([], $sql -> getParameters());
    }


    /**
     * Test delete
     * @return void
     */
    public function testDelete(): void {

        $sql = $this -> builder -> delete() -> table('product') -> where('id > 0') -> orderBy('id', 'desc') -> limit(10) -> build();
        $this -> assertEquals('DELETE FROM product WHERE (id > 0) ORDER BY id DESC LIMIT 10', $sql -> getQuery());
    }


    /**
     * Test inner join, left join, right join, cross join
     * @return void
     */
    public function testJoins(): void {

        $sql = $this -> builder -> select() -> table('product') -> leftJoin('product') -> innerJoin('product') -> rightJoin('product') -> crossJoin('product') -> build();
        $this -> assertEquals('SELECT * FROM product LEFT JOIN product INNER JOIN product RIGHT JOIN product CROSS JOIN product', $sql -> getQuery());

        $sql = $this -> builder -> select() -> table('product a') -> leftJoin('product', 'b', 'b.id = a.id') -> innerJoin('product', 'c', 'c.id = a.id') -> rightJoin('product', 'd', 'd.id = a.id') -> crossJoin('product', 'e') -> build();
        $this -> assertEquals('SELECT * FROM product a LEFT JOIN product b ON b.id = a.id INNER JOIN product c ON c.id = a.id RIGHT JOIN product d ON d.id = a.id CROSS JOIN product e', $sql -> getQuery());

        $sql = $this -> builder -> select() -> table('product a') -> leftJoin('product', null, 'b.id = a.id') -> innerJoin('product', null, 'c.id = a.id') -> rightJoin('product', null, 'd.id = a.id') -> crossJoin('product', null) -> build();
        $this -> assertEquals('SELECT * FROM product a LEFT JOIN product ON b.id = a.id INNER JOIN product ON c.id = a.id RIGHT JOIN product ON d.id = a.id CROSS JOIN product', $sql -> getQuery());
    }


    /**
     * Test update
     * @return void
     */
    public function testUpdate(): void {

        $sql = $this -> builder -> update(['title' => 'test']) -> table('product') -> build();
        $this -> assertEquals('UPDATE product SET title = ?', $sql -> getQuery());
        $this -> assertEquals(['test'], $sql -> getParameters());
    }


    /**
     * Test call
     * @return void
     */
    public function testCall(): void {

        //Call without parameters
        $sql = $this -> builder -> call('test') -> build();
        $this -> assertEquals('CALL test()', $sql -> getQuery());

        //Call with parameters
        $sql = $this -> builder -> call('test') -> parameters([1, 2]) -> build();
        $this -> assertEquals('CALL test(?, ?)', $sql -> getQuery());
        $this -> assertEquals([1, 2], $sql -> getParameters());
    }


    /**
     * Test multiple variables in WHERE IN statement
     * @return void
     */
    public function testWhereIn(): void {

        $sql = $this -> builder -> select() -> table('product') -> where('id = 1 OR id IN(2, 3, 4)') -> build();
        $this -> assertEquals('SELECT * FROM product WHERE (id = 1 OR id IN(2, 3, 4))', $sql -> getQuery());

        $sql = $this -> builder -> select() -> table('product') -> where('id = 1 OR id IN(?)') -> bind([[2, 3, 4]]) -> build();
        $this -> assertEquals('SELECT * FROM product WHERE (id = 1 OR id IN(?,?,?))', $sql -> getQuery());
        $this -> assertEquals([2, 3, 4], $sql -> getParameters());
    }


    /**
     * Testing variable binding
     * @return void
     */
    public function testBindingVariables(): void {

        //Position binding
        $sql = $this -> builder -> select() -> table('product') -> where('id = ? OR id = ?') -> bind([5, 10]);
        $this -> assertEquals('SELECT * FROM product WHERE (id = ? OR id = ?)', $sql -> getQuery());
        $this -> assertEquals([5, 10], $sql -> getParameters());

        //Named binding
        $sql = $this -> builder -> select() -> table('product') -> where('id = :id1 OR id = :id2') -> bind(['id1' => 5, 'id2' => 10]) -> build();
        $this -> assertEquals('SELECT * FROM product WHERE (id = ? OR id = ?)', $sql -> getQuery());
        $this -> assertEquals([5, 10], $sql -> getParameters());
    }
}