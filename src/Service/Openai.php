<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class Openai
{

    private string $apiKey = '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';

    
    public function generateResponse(string $message ): string
    {
        $httpclient = HttpClient::create();
        // Create a completion request
        $response = $httpclient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' =>
            [
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ["role" => "system",
                     "content" => "Réponds avec une personallité type Allemand En 39-45"],

                    ["role" => "user",
                     "content" => $message
                    ],
                ],
            ],
        ]);

        // Decode the JSON response
        $responseData = $response->toArray();


        // Get the generated response text from the completion
          return $responseData['choices'][0]['message']['content'];
    }



}