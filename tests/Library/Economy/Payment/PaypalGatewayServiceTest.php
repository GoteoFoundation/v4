<?php

namespace App\Tests\Library\Economy\Payment;

use App\Library\Economy\Payment\PaypalGatewayService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class PaypalGatewayServiceTest extends KernelTestCase
{
    private PaypalGatewayService $paypalService;

    public function setUp(): void
    {
        self::bootKernel();

        $this->paypalService = static::getContainer()->get(PaypalGatewayService::class);
    }

    public function testAuthenticates(): void
    {
        $expectedResponseData = ['token_type' => 'Bearer', 'expires_in' => 0, 'access_token' => ''];
        $mockResponse = new MockResponse(json_encode($expectedResponseData), [
            'http_code' => 201,
            'response_headers' => ['Content-Type: application/json'],
        ]);

        $httpClient = new MockHttpClient($mockResponse, 'https://testapi.paypal.com');
        $this->paypalService = $this->paypalService->setHttpClient($httpClient);

        $responseData = $this->paypalService->generateAuthToken();

        $this->assertSame('POST', $mockResponse->getRequestMethod());
        $this->assertSame('https://testapi.paypal.com/v1/oauth2/token', $mockResponse->getRequestUrl());

        $this->assertArrayHasKey('token_type', $responseData);
        $this->assertSame($expectedResponseData['token_type'], $responseData['token_type']);

        $this->assertArrayHasKey('expires_in', $responseData);
        $this->assertIsInt($responseData['expires_in']);

        $this->assertArrayHasKey('access_token', $responseData);
        $this->assertIsString($responseData['access_token']);
    }
}
