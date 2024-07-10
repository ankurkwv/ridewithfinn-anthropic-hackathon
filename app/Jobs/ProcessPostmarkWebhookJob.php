<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPostmarkWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $requestInput;

    /**
     * Create a new job instance.
     */
    public function __construct($requestInput)
    {
        $this->requestInput = $requestInput;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info($this->requestInput);
    }
}
