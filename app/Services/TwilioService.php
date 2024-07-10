<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class TwilioService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.api_key'),
            config('services.twilio.api_secret'),
            config('services.twilio.account_sid')
        );
    }

    public function sendSMS($to, $message)
    {
        try {
            $result = $this->client->messages->create(
                $to,
                [
                    'from' => config('services.twilio.phone_number'),
                    'body' => $message,
                ]
            );

            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'sid' => $result->sid,
            ];
        } catch (TwilioException $e) {
            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage(),
            ];
        }
    }
}