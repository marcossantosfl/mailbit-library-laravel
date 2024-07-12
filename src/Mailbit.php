<?php

namespace Marcos\MailbitLibraryLaravel;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class Mailbit
{
    protected $apiKey;
    protected $client;
    protected $baseUrl = 'https://public-api.mailbit.io';

    public function __construct($apiKey)
    {
        if (!$apiKey) {
            throw new \InvalidArgumentException("API key is required");
        }

        $this->apiKey = $apiKey;

        $stack = HandlerStack::create();

        // Add a middleware to add the token to every request
        $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader('token', $this->apiKey);
        }));

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'handler' => $stack,
            'timeout' => 30.0,
            'headers' => [
                'Connection' => 'keep-alive',
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function sendEmail(array $emailData)
    {
        $url = '/send-email';

        try {
            $response = $this->client->post($url, [
                'json' => $emailData
            ]);

            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $code = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents(), true);

            if (isset($responseBody['errors']) && is_array($responseBody['errors'])) {
                $errors = $responseBody['errors'];
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = "Code: {$error['code']}, Message: {$error['message']}";
                }
                $message = implode(" | ", $errorMessages);
                $errorCode = $code;
            } else {
                $message = 'No error message provided';
                $errorCode = 'Unknown';
            }

            throw new \Exception("Error sending email\nCode: {$errorCode}\nMessage: {$message}");
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $code = $response->getStatusCode();
                $responseBody = json_decode($response->getBody()->getContents(), true);

                if (isset($responseBody['errors']) && is_array($responseBody['errors'])) {
                    $errors = $responseBody['errors'];
                    $errorMessages = [];
                    foreach ($errors as $error) {
                        $errorMessages[] = "Code: {$error['code']}, Message: {$error['message']}";
                    }
                    $message = implode(" | ", $errorMessages);
                    $errorCode = $code;
                } else {
                    $message = 'No error message provided';
                    $errorCode = 'Unknown';
                }

                throw new \Exception("Error sending email\nCode: {$errorCode}\nMessage: {$message}");
            } else {
                throw new \Exception("Error sending email - No response received: " . $e->getMessage());
            }
        } catch (\Exception $e) {
            throw new \Exception("Error sending email - General error: " . $e->getMessage());
        }
    }
}
