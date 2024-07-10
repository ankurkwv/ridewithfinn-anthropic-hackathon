<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessPostmarkWebhookJob;

class PostmarkController extends Controller
{
    
    public $timeout = 120;

    /**
     * Handle incoming webhook
     */
    public function index(Request $request)
    {  
    	\Log::info('Dispatching job...');

        ProcessPostmarkWebhookJob::dispatch([$request->input('HtmlBody'), $request->input('TextBody')]);
 
    	return response()->json(null, 201);
    }
}
