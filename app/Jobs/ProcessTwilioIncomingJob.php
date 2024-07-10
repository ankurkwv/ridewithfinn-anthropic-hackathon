<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\AnthropicClient;
use App\Services\AirtableService;
use App\Services\PromptLibrary;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class ProcessTwilioIncomingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $requestInput;
    private AnthropicClient $ai;
    private AirtableService $airtable;
    private PromptLibrary $promptLibrary;
    private TwilioService $twilio;

    /**
     * Create a new job instance.
     *
     * @param array $requestInput
     */
    public function __construct(array $requestInput)
    {
        $this->requestInput = $requestInput;
    }

    /**
     * Execute the job.
     *
     * @throws ValidationException
     */
    public function handle(
        AnthropicClient $ai,
        AirtableService $airtable,
        PromptLibrary $promptLibrary,
        TwilioService $twilio
    ): void {
        $this->ai = $ai;
        $this->airtable = $airtable;
        $this->promptLibrary = $promptLibrary;
        $this->twilio = $twilio;
        \Log::info('Processing job picked & started...');

        $budgets = $this->airtable->getBudgets();
        $prompt = $this->promptLibrary->createExpensePrompt($budgets, $this->requestInput['message']);

        \Log::info('Prompt prepared, sending to Anthropic...');

        $response = $this->getAIResponse($prompt);

        \Log::info('AI response received...');
        $this->processAIResponse($response);
    }

    /**
     * Get AI response with caching.
     *
     * @param array $prompt
     * @return array
     */
    private function getAIResponse(array $prompt): array
    {
        $cacheKey = 'ai_response_' . md5(json_encode($prompt));
        return Cache::remember($cacheKey, 1000, function () use ($prompt) {
            return $this->ai->generateResponse(
                [["role" => "user", "content" => $prompt['user']]],
                [
                    "system" => $prompt['system'],
                    "max_tokens" => $prompt['maxTokens'],
                    "tools" => $prompt['tools']
                ]
            );
        });
    }

    /**
     * Process AI response.
     *
     * @param array $response
     * @throws ValidationException
     */
    private function processAIResponse(array $response): void
    {
        foreach ($response['content'] as $content) {
            if ($this->isConfirmationMessage($content)) {
                $this->sendConfirmationSMS($content);
            } elseif ($this->isCreateExpenseToolUse($content)) {
                $this->createExpense($content['input']);
            }
        }
    }

    /**
     * Check if the content is a confirmation message.
     *
     * @param array $content
     * @return bool
     */
    private function isConfirmationMessage(array $content): bool
    {
        return isset($content['type'])
            && $content['type'] === 'text'
            && preg_match('/<confirmation>(.*?)<\/confirmation>/s', $content['text'], $matches);
    }

    /**
     * Send confirmation SMS.
     *
     * @param array $content
     */
    private function sendConfirmationSMS(array $content): void
    {
        preg_match('/<confirmation>(.*?)<\/confirmation>/s', $content['text'], $matches);
        $message = str_replace("\n", "", $matches[1]);
        $this->twilio->sendSMS($this->requestInput['from'], $message);
        \Log::info('Sent SMS confirmation...');
    }

    /**
     * Check if the content is a create expense tool use.
     *
     * @param array $content
     * @return bool
     */
    private function isCreateExpenseToolUse(array $content): bool
    {
        return isset($content['type'])
            && $content['type'] === 'tool_use'
            && $content['name'] === 'createExpense';
    }

    /**
     * Create an expense.
     *
     * @param array $input
     * @throws ValidationException
     */
    private function createExpense(array $input): void
    {
        $validator = Validator::make($input, [
            'expenseOrMerchantName' => 'required|string|max:500',
            'amount' => 'required|numeric',
            'budget' => 'required|string|regex:/^rec/',
            'notes' => 'nullable|string|max:500',
            'nightsSpent' => 'nullable|numeric',
            'startDate' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->airtable->createExpense($input);
        \Log::info('Created expense record! Finished.', $record);
    }
}