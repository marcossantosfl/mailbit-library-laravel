<?php

namespace Marcos\MailbitLibraryLaravel;

use GuzzleHttp\Client;

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
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => ['token' => $this->apiKey]
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
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $code = $response->getStatusCode();
            $message = json_decode($response->getBody()->getContents(), true)['message'] ?? 'No error message provided';

            throw new \Exception("Error sending email\nCode: {$code}\nMessage: {$message}");
        } catch (\Exception $e) {
            throw new \Exception("Error sending email - General error: " . $e->getMessage());
        }
    }
}
