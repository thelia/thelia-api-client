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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\Exception\ClientException;
use Thelia\Api\Client\Client;

/**
 * Class ClientTest
 * @package Thelia\Api\Client\Tests
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp(): void
    {
        (new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

        $this->client = new Client(
            $_ENV['API_KEY'],
            $_ENV['API_TOKEN'],
            $_ENV['API_BASE_URL']
        );
    }

    public function testClientReturnsAnArrayOnGetAction()
    {
        list($status, $data) = $this->client->doList("products");

        $this->assertEquals(200, $status);

        $this->assertIsArray($data);
        $this->assertGreaterThan(0, count($data));
    }

    public function testClientReturnsGoodValuesWithLoopParameters()
    {
        /**
         * Test one locale
         */
        list($status, $data) = $this->client->doList("products", ["lang" => 'fr_FR']);

        $this->assertEquals(200, $status);

        $this->assertIsArray($data);
        $this->assertGreaterThan(0, count($data));
        $this->assertArrayHasKey('LOCALE', $data[0]);
        $this->assertEquals("fr_FR", $data[0]['LOCALE']);

        /**
         * Test another
         */
        list($status, $data) = $this->client->doList("products", ["lang" => 'en_US']);

        $this->assertEquals(200, $status);

        $this->assertIsArray($data);
        $this->assertGreaterThan(0, count($data));
        $this->assertArrayHasKey('LOCALE', $data[0]);
        $this->assertEquals("en_US", $data[0]['LOCALE']);
    }

    public function testRetrievesArrayFromUrlParameters()
    {
        $params = "var1=foo&var2=bar&foo";

        $this->assertEquals(
            [
                "var1" => "foo",
                "var2" => "bar",
                "foo" => '',
            ],
            $this->client->retrieveArrayFromUrlParameters($params)
        );
    }

    public function testRetrievesArrayFromUrlParametersEvenWithWeirdCombinations()
    {
        $params = "&var1=foo&&var2=bar&foo&";

        $this->assertEquals(
            [
                "var1" => "foo",
                "var2" => "bar",
                "foo" => '',
            ],
            $this->client->retrieveArrayFromUrlParameters($params)
        );
    }

    public function testTransformsArrayToUrlParameterString()
    {
        $params = array(
            "var1" => "foo",
            "var2" => "bar",
            "foo" => '',
        );

        $this->assertEquals(
            "var1=foo&var2=bar&foo",
            $this->client->retrieveUrlParametersFromArray($params)
        );
    }

    public function testTransformsArrayToUrlParameterStringEventWithWeirdArray()
    {
        $params = array(
            "var1" => "foo",
            "var2" => "bar",
            "foo" => '',
            "bar" => "baz",
            "baz" => null,
        );

        $this->assertEquals(
            "var1=foo&var2=bar&foo&bar=baz&baz",
            $this->client->retrieveUrlParametersFromArray($params)
        );
    }

    public function testDoesNotThrowExceptionOnError()
    {
        list($status, $data) = $this->client->doGet("products", PHP_INT_MAX);

        $this->assertEquals(404, $status);
        $this->assertIsArray($data);

        $this->assertArrayHasKey("error", $data);
    }

    public function testDoesThrowExceptionOnError()
    {
        $this->expectException(ClientException::class);

        $client = new Client(
            $_ENV['API_KEY'],
            $_ENV['API_TOKEN'],
            $_ENV['API_BASE_URL'],
            null,
            '/api/',
            true
        );

        $client->doGet("products", PHP_INT_MAX);
    }

    public function testConvertsSnakeCaseToCamelCase()
    {
        $this->assertEquals(
            "helloWorld",
            Client::snakeToCamelCase("hello-world")
        );

        $this->assertEquals(
            "thisIsALongSentence",
            Client::snakeToCamelCase("this-is-a-long-sentence")
        );
    }

    public function testConvertsSnakeCaseToPascalCase()
    {
        $this->assertEquals(
            "HelloWorld",
            Client::snakeToPascalCase("hello-world")
        );

        $this->assertEquals(
            "ThisIsALongSentence",
            Client::snakeToPascalCase("this-is-a-long-sentence")
        );
    }

    public function testConvertsCamelCaseToSnakeCase()
    {
        $this->assertEquals(
            "hello-world",
            Client::camelToSnakeCase("helloWorld")
        );

        $this->assertEquals(
            "this-is-a-long-sentence",
            Client::camelToSnakeCase("thisIsALongSentence")
        );
    }

    public function testConvertsPascalCaseToSnakeCase()
    {
        $this->assertEquals(
            "hello-world",
            Client::pascalToSnakeCase("HelloWorld")
        );

        $this->assertEquals(
            "this-is-a-long-sentence",
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

    public function testCanCallCamelizedDashOnMagicCall()
    {
        list($status, $data) = $this->client->listAttributeAvs();

        $this->assertEquals(200, $status);
    }
}

