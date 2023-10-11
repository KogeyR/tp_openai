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
                    ["role" => "system", "content" => "Réponds comme une bourge"],
                    ["role" => "assistant", "content" => "Quelle est le plat préférer des stephanois"],
                    ["role" => "user", "content" => $message],
                ],
            ],
        ]);


        // Decode the JSON response
        $responseData = $response->toArray();


        $assistantEntity = new Message();
        $assistantEntity->setContent($responseData['choices'][0]['message']['content']);
        $assistantEntity->setCreatedAt(new \DateTimeImmutable());
        $assistantEntity->setRole('assistant');

        $messageEntity = new Message();
        $messageEntity->setContent($message);
        $messageEntity->setCreatedAt(new \DateTimeImmutable());
        $messageEntity->setRole('user');

        $this->entityManager->persist($messageEntity);
        $this->entityManager->persist($assistantEntity);

        $this->entityManager->flush();

        // Get the generated response text from the completion
        return $responseData['choices'][0]['message']['content'];
    }

    public function getConversationHistory(): array
{
    $messageRepository = $this->entityManager->getRepository(Message::class);
    $messages = $messageRepository->findAll();
    $history = [];

    foreach ($messages as $message) {
        $history[] = [
            'role' => $message->getRole(),
            'content' => $message->getContent(),
        ];
    }

    return $history;
}
}