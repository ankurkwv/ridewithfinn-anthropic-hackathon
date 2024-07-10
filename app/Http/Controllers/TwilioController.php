<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessTwilioIncomingJob;

class TwilioController extends Controller
{

    /**
     * Correctly route the incoming message.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        \Log::info('Incoming Twilio request...');
        $data = $this->getMessageData($request);

        ProcessTwilioIncomingJob::dispatch($data);
        \Log::info('Dispatched processing job...');

        return $this->safeTwilioResponse();
    }

    private function safeTwilioResponse()
    {
        return response("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response/>", 200, ['Content-Type' => 'text/xml']);
    }

    private function getMessageData($request)
    {
        $text = trim($request['Body'], $character_mask = " \t\r\0\x0B");

        $data = [
            'sid' => $request['MessageSid'],
            'from' => $request['From'],
            'to' => $request['To'],
            'time' => microtime(true),
            'message' => $text,
        ];

        if (isset($request['OptOutType'])) {
            $data['OptOutType'] = $request['OptOutType'];
        }

        return $data;
    }
}
