<?php


namespace App\Service;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAi
{
    private $entityManager;
    private $openaiApiKey = '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 ';

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generateResponse(string $message): string
    {
        $httpclient = HttpClient::create();
        // Create a completion request
        $response = $httpclient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ["role" => "system", "content" => "Réponds de façon condescendante"],
                    ["role" => "assistant", "content" => "Quelle est la capitale de la France"],
                    ["role" => "user", "content" => $message],
                ],
            ],
        ]);


        // Decode the JSON response
        $responseData = $response->toArray();


        $assistantEntity = new Message();
        $messageEntity = new Message();
        $messageEntity->setContent($message);
        $messageEntity->setCreatedAt(new \DateTimeImmutable());
        $messageEntity->setRole('user');

        $assistantEntity->setContent($responseData['choices'][0]['message']['content']);
        $assistantEntity->setCreatedAt(new \DateTimeImmutable());
        $assistantEntity->setRole('assistant');

        $this->entityManager->persist($messageEntity);
        $this->entityManager->persist($assistantEntity);

        $this->entityManager->flush();

        // Get the generated response text from the completion
        return $responseData['choices'][0]['message']['content'];
    }

    public function getConversationHistory(): array
    {
        $messageRepository = $this->entityManager->getRepository(Message::class);
        $messages = $messageRepository->findBy([], ['createdAt' => 'DESC'], 10); 
        $history = [];
    
        foreach ($messages as $message) {
            $history[] = [
                'role' => $message->getRole(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAtAsString(),
            ];
        }
    
      
        $history = array_reverse($history);
    
        return $history;
    }
    
}