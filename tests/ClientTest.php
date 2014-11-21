<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace Thelia\Api\Client\Tests;
use Thelia\Api\Client\Client;

/**
 * Class ClientTest
 * @package Thelia\Api\Client\Tests
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    protected $client;

    protected $baseUrl;

    public function setUp()
    {
        $this->client = new Client(
            "79E95BD784CADA0C9A578282E",
            "B45B9F244866F77E53255D6C0E0B60A2FA295CB0CFE25",
            $this->baseUrl = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "server.txt")
        );
    }

    public function testClientReturnsAnArrayOnGetAction()
    {
        list($status, $data) = $this->client->doList("products");

        $this->assertEquals(200, $status);

        $this->assertTrue(is_array($data));
        $this->assertGreaterThan(0, count($data));
    }

    public function testClientReturnsGoodValuesWithLoopParameters()
    {
        /**
         * Test one locale
         */
        list($status, $data) = $this->client->doList("products", ["lang" => 'fr_FR']);

        $this->assertEquals(200, $status);

        $this->assertTrue(is_array($data));
        $this->assertGreaterThan(0, count($data));
        $this->assertArrayHasKey('LOCALE', $data[0]);
        $this->assertEquals("fr_FR", $data[0]['LOCALE']);

        /**
         * Test another
         */
        list($status, $data) = $this->client->doList("products", ["lang" => 'en_US']);

        $this->assertEquals(200, $status);

        $this->assertTrue(is_array($data));
        $this->assertGreaterThan(0, count($data));
        $this->assertArrayHasKey('LOCALE', $data[0]);
        $this->assertEquals("en_US", $data[0]['LOCALE']);
    }

    public function testDoesNotThrowExceptionOnError()
    {
        list($status, $data) = $this->client->doGet("products", PHP_INT_MAX);

        $this->assertEquals(404, $status);
        $this->assertTrue(is_array($data));

        $this->assertArrayHasKey("error", $data);
    }

    public function testConvertsSnakeCaseToCamelCase()
    {
        $this->assertEquals(
            "helloWorld",
            Client::snakeToCamelCase("hello_world")
        );

        $this->assertEquals(
            "thisIsALongSentence",
            Client::snakeToCamelCase("this_is_a_long_sentence")
        );
    }

    public function testConvertsSnakeCaseToPascalCase()
    {
        $this->assertEquals(
            "HelloWorld",
            Client::snakeToPascalCase("hello_world")
        );

        $this->assertEquals(
            "ThisIsALongSentence",
            Client::snakeToPascalCase("this_is_a_long_sentence")
        );
    }

    public function testConvertsCamelCaseToSnakeCase()
    {
        $this->assertEquals(
            "hello_world",
            Client::camelToSnakeCase("helloWorld")
        );

        $this->assertEquals(
            "this_is_a_long_sentence",
            Client::camelToSnakeCase("thisIsALongSentence")
        );
    }

    public function testConvertsPascalCaseToSnakeCase()
    {
        $this->assertEquals(
            "hello_world",
            Client::pascalToSnakeCase("HelloWorld")
        );

        $this->assertEquals(
            "this_is_a_long_sentence",
            Client::pascalToSnakeCase("ThisIsALongSentence")
        );
    }

    public function testAcceptMagicCalls()
    {
        $expected = $this->client->doList("products", ["lang" => 'fr_FR']);
        $current = $this->client->listProducts(["lang" => "fr_FR"]);

        $this->assertEquals($expected, $current);

        $expected = $this->client->doGet("products", 1);
        $current = $this->client->getProducts(1);

        $this->assertEquals($expected, $current);
    }
}
 