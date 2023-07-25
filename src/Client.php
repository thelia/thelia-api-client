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

namespace Thelia\Api\Client;

use BadMethodCallException as BadMethodCallExceptionAlias;
use Exception as ExceptionAlias;
use JsonException as JsonExceptionAlias;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class Client
 * @package Thelia\Api\Client
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class Client
{
    public static string $snakeSeparator = '-';

    protected static array $knownMethods = array(
        "LIST",
        "GET",
        "POST",
        "PUT",
        "DELETE"
    );

    /**
     * @param string $apiToken
     * @param string $apiKey
     * @param string $baseUrl
     * @param HttpClientInterface|null $client
     * @param string $baseApiRoute
     * @param bool $throwExceptions
     */
    public function __construct(
        protected string $apiToken,
        protected string $apiKey,
        protected string $baseUrl = '',
        protected ?HttpClientInterface $client = null,
        protected string $baseApiRoute = '/api/',
        protected bool $throwExceptions = false
    ) {
        if (null === $client) {
            $this->client = HttpClient::create();
        }
    }

    // Api Actions

    /**
     * @param string $name
     * @param array $loopArgs
     * @param array $headers
     * @param array $options
     * @return array|ResponseInterface|null
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws JsonExceptionAlias
     */
    public function doList(string $name, array $loopArgs = array(), array $headers = array(), array $options = array()): array|ResponseInterface|null
    {
        return $this->call(
            "GET",
            $this->baseApiRoute . $name,
            $loopArgs,
            '',
            $headers,
            $options
        );
    }

    /**
     * @param string $name
     * @param $id
     * @param array $loopArgs
     * @param array $headers
     * @param array $options
     * @return array|ResponseInterface
     * @throws ClientExceptionInterface
     * @throws JsonExceptionAlias
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function doGet(string $name, $id, array $loopArgs = array(), array $headers = array(), array $options = array()): array|ResponseInterface
    {
        return $this->call(
            "GET",
            $this->baseApiRoute . $name . '/' . $id,
            $loopArgs,
            '',
            $headers,
            $options
        );
    }

    /**
     * @param string $name
     * @param $body
     * @param array $loopArgs
     * @param array $headers
     * @param array $options
     * @return array|ResponseInterface
     * @throws ClientExceptionInterface
     * @throws JsonExceptionAlias
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function doPost(string $name, $body, array $loopArgs = array(), array $headers = array(), array $options = array()): array|ResponseInterface
    {
        if (is_array($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        }

        return $this->call(
            "POST",
            $this->baseApiRoute . $name,
            $loopArgs,
            $body,
            array_merge(
                [
                    "Content-Type" => "application/json"
                ],
                $headers
            ),
            $options
        );
    }

    /**
     * @param string $name
     * @param $body
     * @param $id
     * @param array $loopArgs
     * @param array $headers
     * @param array $options
     * @return array|ResponseInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws JsonExceptionAlias
     */
    public function doPut(string $name, $body, $id = null, array $loopArgs = array(), array $headers = array(), array $options = array()): array|ResponseInterface
    {
        if (is_array($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        }

        if (null !== $id && '' !== $id) {
            $id = '/' . $id;
        }

        return $this->call(
            "PUT",
            $this->baseApiRoute . $name . $id,
            $loopArgs,
            $body,
            array_merge(
                [
                    "Content-Type" => "application/json"
                ],
                $headers
            ),
            $options
        );
    }

    /**
     * @param string $name
     * @param $id
     * @param array $loopArgs
     * @param array $headers
     * @param array $options
     * @return array|ResponseInterface
     * @throws ClientExceptionInterface
     * @throws JsonExceptionAlias
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function doDelete(string $name, $id, array $loopArgs = array(), array $headers = array(), array $options = array()): array|ResponseInterface
    {
        return $this->call(
            "DELETE",
            $this->baseApiRoute . $name . '/' . $id,
            $loopArgs,
            '',
            $headers,
            $options
        );
    }

    // Client Routines

    /**
     * @param string $method
     * @param string $pathInfo
     * @param array $queryParameters
     * @param string $body
     * @param array $headers
     * @param array $options
     * @return array|ResponseInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws JsonExceptionAlias
     */
    public function call(string $method, string $pathInfo, array $queryParameters = array(), string $body = '', array $headers = array(),  array $options = array()): array|ResponseInterface
    {
        $url = $this->baseUrl . $pathInfo;

        return $this->callUrl($method, $url, $queryParameters, $body, $headers, $options);
    }

    /**
     * @param string $method
     * @param string $fullUrl
     * @param array $query
     * @param string $body
     * @param array $headers
     * @param array $options
     * @return array|ResponseInterface
     * @throws JsonExceptionAlias
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function callUrl(string $method, string $fullUrl, array $query = array(), string $body = '', array $headers = array(),  array $options = array()): array|ResponseInterface
    {
        $response = $this->prepareRequest($method, $fullUrl, $query, $body, $headers, $options);

        if ($options["handle_response"] ?? false) {
            return $response;
        }

        return $this->handleResponse($response);
    }

    /**
     * @param ResponseInterface $response
     * @return array
     * @throws JsonExceptionAlias
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function handleResponse(ResponseInterface $response): array
    {
        try {
            $body = $response->getContent();

            if ($response->getHeaders()['content-type'][0]) {
                $body = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            }
        } catch (ExceptionAlias $ex) {
            if ($this->throwExceptions) {
                throw $ex;
            }

            $body = [ 'error' => $ex->getMessage() ];
        }

        return [$response->getStatusCode(), $body ];
    }

    /**
     * @param $method
     * @param $fullUrl
     * @param array $query
     * @param string $body
     * @param array $headers
     * @param array $options
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function prepareRequest($method, $fullUrl, array $query = array(), string $body = '', array $headers = array(), array $options = array()): ResponseInterface
    {
        $requestOptions = array_merge([
            'body' => $body,
            'headers' => array_merge(
                [ "Authorization" => "TOKEN " . $this->apiToken ],
                $headers
            )
        ], $options);

        $query["sign"] = $this->getSignature($body);

        $fullUrl = $this->formatUrl($fullUrl, $query);

        return $this->client->request(
            $method,
            $fullUrl,
            $requestOptions
        );
    }

    /**
     * @param $url
     * @param array $params
     * @return string
     */
    protected function formatUrl($url, array $params): string
    {
        if (str_contains($url, '?')) {
            [$url, $values] = explode('?', $url, 1);

            $params = array_merge(
                $this->retrieveArrayFromUrlParameters($values),
                $params
            );
        }

        $urlParameters = $this->retrieveUrlParametersFromArray($params);

        if ($urlParameters !== '') {
            $urlParameters = '?' . $urlParameters;
        }

        return $url . $urlParameters ;
    }

    // Client helpers

    /**
     * @param array $params
     * @return string
     */
    public function retrieveUrlParametersFromArray(array $params): string
    {
        $string = '';

        foreach ($params as $key => $value) {
            $string .= $key;

            if ($value !=='' && $value !== null) {
                $string .= '=' . $value;
            }

            $string .= '&';
        }

        if ($string === '') {
            return $string;
        }

        return substr($string, 0, -1);
    }

    /**
     * @param $strParams
     * @return array
     */
    public function retrieveArrayFromUrlParameters($strParams): array
    {
        $table = array();

        $len = strlen($strParams);
        $key = '';
        $value = '';
        $toggle = false;

        for ($i = 0; $i < $len; ++$i) {
            if ($strParams[$i] === '&') {
                if ($key !== '') {
                    // Store current var
                    $table[$key] = $value;

                    // Re-init values
                    $key = '';
                    $value = '';
                    $toggle = false;
                }
            } elseif ($strParams[$i] === '=') {
                $toggle = true;
            } elseif ($toggle) {
                $value .= $strParams[$i];
            } else {
                $key .= $strParams[$i];
            }
        }

        // Collect the last
        if (!empty($key)) {
            $table[$key] = $value;
        }

        return $table;
    }

    /**
     * @param string $requestContent
     * @return string
     */
    protected function getSignature(string $requestContent = ''): string
    {
        $secureKey = pack('H*', $this->apiKey);

        return hash_hmac('sha1', $requestContent, $secureKey);
    }


    // Magic calls
    public function __call(string $name, $arguments)
    {
        $callable = null;

        if (method_exists($this, $name)) {
            $callable = [$this, $name];
        }

        foreach (static::$knownMethods as $method) {
            if (str_starts_with($name, strtolower($method)) && strlen($name) > $methodLen = strlen($method)) {
                $entity = static::pascalToSnakeCase(substr($name, $methodLen));

                $methodName = 'do'.ucfirst(strtolower($method));

                if (method_exists($this, $methodName)) {
                    $callable = [$this, $methodName];
                }

                array_unshift($arguments, $entity);
                break;
            }
        }

        if (null !== $callable) {
            return call_user_func_array($callable, $arguments);
        }

        throw new BadMethodCallExceptionAlias(
            sprintf("The method %s::%s doesn't exist", __CLASS__, $name)
        );
    }

    // Formatting tools

    public static function snakeToCamelCase($value): array|string|null
    {
        $separator = static::$snakeSeparator;

        return preg_replace_callback(
            "/\\{$separator}([a-z])/i",
            static function($match) {
                return strtoupper($match[1]);
            },
            $value
        );
    }

    public static function snakeToPascalCase(string $value): string
    {
        return ucfirst(static::snakeToCamelCase($value));
    }

    public static function camelToSnakeCase(string $value): string
    {
        return preg_replace_callback(
            "/([A-Z])/",
            static function($match) {
                return static::$snakeSeparator . strtolower($match[1]);
            },
            $value
        );
    }

    public static function pascalToSnakeCase(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        $value = strtolower($value[0]) . substr($value, 1);

        return static::camelToSnakeCase($value);
    }
}
