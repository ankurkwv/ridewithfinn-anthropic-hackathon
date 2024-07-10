<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class AnthropicClient
{
    /**
     * @var string
     */
    private string $apiKey;

    /**
     * @var string
     */
    private string $baseUrl;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * AnthropicClient constructor.
     */
    public function __construct()
    {
        $this->apiKey = Config::get('services.anthropic.api_key');
        $this->baseUrl = 'https://api.anthropic.com';
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => "2023-06-01",
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Send a request to the Anthropic API.
     *
     * @param string $endpoint
     * @param string $method
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function sendRequest(string $endpoint, string $method = 'POST', array $data = []): array
    {
        try {
            \Log::info($data);
            $response = $this->client->request($method, $endpoint, [
                'json' => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Anthropic API request failed', [
                'error' => $e->getMessage(),
                'endpoint' => $endpoint,
                'method' => $method,
                'data' => $data,
            ]);

            throw new \Exception('Failed to communicate with Anthropic API: ' . $e->getMessage());
        }
    }

    /**
     * Generate a response using the Claude API.
     *
     * @param string $messages
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function generateResponse(array $messages, array $options = []): array
    {
        $data = array_merge([
            'model' => 'claude-3-5-sonnet-20240620',
            'max_tokens' => 1000,
            'messages' => $messages,
            'temperature' => 0.1,
        ], $options);

        return $this->sendRequest('/v1/messages', 'POST', $data);
    }
}