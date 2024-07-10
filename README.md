## Ride With Finn

Why is this project called "Ride With Finn"? Well, Finn is our dog, and we are taking a 34-state, 4-month road trip, and this repository was created to help us keep track of our budget while we ride with Finn!

## About This App

This is a Laravel app – a PHP framework with the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks.

A user texts a Twilio number a new request to store an expense, then the app will fetch possible budget categories from their own Budget Airtable and ask Anthropic's Claude Sonnet 3.5 model to classify and extract the information using Tools. Finally, the information is stored in Airtable and a confirmation text is sent to the user.

<p align="center"><img src="https://bubbles-armadillo-5455.twil.io/assets/Anthropic%20Hackathon.png" width="400" alt="Flow Chart"></p>

## Important Files
If you wish to review the code, here are files I would point out to you.
1. `ProcessTwilioIncomingJob.php`: The core "glue file" where you will find calls to Airtable, Twilio, and Anthropic services.
2. `AirtableService.php`: Code for extracting & storing in Airtable. 
3. `AnthropicClient.php`: Code for prompting Anthropic APIs.
4. `TwilioService.php`: Code for working with Twilio APIs.
5. `TwilioController.php`: Code for immediate processing of the incoming webhook.
6. `PromptLibrary.php`: My favorite idea of this project. I feel like we will all need a new method for storing and organizing prompts. This project led me to reuse Laravel's templating engine (Blade) to store & process prompts.

## Environment Variables
Be sure to fill out the following environment variables into your `.env` file.
- `AIRTABLE_KEY`
- `AIRTABLE_BASE`
- `AIRTABLE_TABLE`

- `AIRTABLE_TYPECAST`
- `ANTHROPIC_API_KEY`

- `TWILIO_ACCOUNT_SID`
- `TWILIO_API_KEY`
- `TWILIO_API_SECRET`
- `TWILIO_PHONE_NUMBER`

## Future Ideas
1. I want to, and have begun, to create a Postmark driver that will be used for forwarding in receipts for automatic storage and categorization.
2. Our Airtable stores other information about our trip, and I want to enable Q&A and more via the SMS interface.