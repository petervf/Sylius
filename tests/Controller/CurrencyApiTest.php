<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Tests\Controller;

use Lakion\ApiTestCase\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Axel Vankrunkelsven <axel@digilabs.be>
 */
class CurrencyApiTest extends JsonApiTestCase
{
    /**
     * @var array
     */
    private static $authorizedHeader = [
        'HTTP_Authorization' => 'Bearer SampleTokenNjZkNjY2MDEwMTAzMDkxMGE0OTlhYzU3NzYyMTE0ZGQ3ODcyMDAwM2EwMDZjNDI5NDlhMDdlMQ',
    ];

    /**
     * @var array
     */
    private static $authorizedHeaderWithContentType = [
        'HTTP_Authorization' => 'Bearer SampleTokenNjZkNjY2MDEwMTAzMDkxMGE0OTlhYzU3NzYyMTE0ZGQ3ODcyMDAwM2EwMDZjNDI5NDlhMDdlMQ',
        'CONTENT_TYPE' => 'application/json',
    ];

    /**
     * @var array
     */
    private static $authorizedHeaderWithAccept = [
        'HTTP_Authorization' => 'Bearer SampleTokenNjZkNjY2MDEwMTAzMDkxMGE0OTlhYzU3NzYyMTE0ZGQ3ODcyMDAwM2EwMDZjNDI5NDlhMDdlMQ',
        'ACCEPT' => 'application/json',
    ];

    /**
     * @test
     */
    public function test_create_currency_access_denied_response()
    {
        $this->client->request('POST', '/api/currencies/');

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'authentication/access_denied_response', Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function test_create_currency_validation_fail_response()
    {
        $this->loadFixturesFromFile('authentication/api_administrator.yml');

        $this->client->request('POST', '/api/currencies/', [], [], static::$authorizedHeaderWithContentType);

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'currency/create_validation_fail_response', Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     */
    public function test_create_currency_response()
    {
        $this->loadFixturesFromFile('authentication/api_administrator.yml');

        $data =
<<<EOT
        {
            "code": "USD"
        }
EOT;

        $this->client->request('POST', '/api/currencies/', [], [], static::$authorizedHeaderWithContentType, $data);

        $response = $this->client->getResponse();

        $this->assertResponse($response, 'currency/create_response', Response::HTTP_CREATED);
    }

    /**
     * @test
     */
    public function test_get_currencies_list_access_denied_response()
    {
        $this->client->request('GET', '/api/currencies/');

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'authentication/access_denied_response', Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function test_get_currencies_list_response()
    {
        $this->loadFixturesFromFile('authentication/api_administrator.yml');
        $this->loadFixturesFromFile('resources/currencies.yml');

        $this->client->request('GET', '/api/currencies/', [], [], static::$authorizedHeader);

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'currency/index_response', Response::HTTP_OK);
    }

    /**
     * @test
     */
    public function test_get_currency_access_denied_response()
    {
        $this->client->request('GET', '/api/currencies/1');

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'authentication/access_denied_response', Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function test_get_currency_which_does_not_exist_response()
    {
        $this->loadFixturesFromFile('authentication/api_administrator.yml');

        $this->client->request('GET', '/api/currencies/-1', [], [], static::$authorizedHeaderWithAccept);

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'error/not_found_response', Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     */
    public function test_get_currency_response()
    {
        $this->loadFixturesFromFile('authentication/api_administrator.yml');
        $currencies = $this->loadFixturesFromFile('resources/currencies.yml');

        $this->client->request('GET', '/api/currencies/'.$currencies['currency_1']->getCode(), [], [], static::$authorizedHeaderWithAccept);

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'currency/show_response', Response::HTTP_OK);
    }

    /**
     * @test
     */
    public function test_delete_currency_which_does_not_exist_response()
    {
        $this->loadFixturesFromFile('authentication/api_administrator.yml');

        $this->client->request('DELETE', '/api/currencies/-1', [], [], static::$authorizedHeaderWithAccept);

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'error/not_found_response', Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     */
    public function test_delete_currency_response()
    {
        $this->loadFixturesFromFile('authentication/api_administrator.yml');
        $currencies = $this->loadFixturesFromFile('resources/currencies.yml');

        $this->client->request('DELETE', '/api/currencies/'.$currencies['currency_1']->getCode(), [], [], static::$authorizedHeaderWithContentType, []);

        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_NO_CONTENT);

        $this->client->request('GET', '/api/currencies/'.$currencies['currency_1']->getCode(), [], [], static::$authorizedHeaderWithAccept);

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'error/not_found_response', Response::HTTP_NOT_FOUND);
    }
}
