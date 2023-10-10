<?php

namespace App\Controller;

use App\Service\Openai;
use App\Form\OpenaiType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;



class ChatController extends AbstractController
{
    private $httpClient;
    private $openAiService;

    public function __construct(HttpClientInterface $httpClient, Openai $openAi)
    {
        $this->httpClient = $httpClient;
        $this->openAiService = $openAi;
    }

    #[Route('/chat', name: 'app_chat', methods: ['GET', 'POST'])]
    public function chat(Request $request): Response
    {
        $form = $this->createForm(OpenaiType::class);
        $form->handleRequest($request);

        $responseText = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userInput = $data['userInput'];

            try {
                $responseText = $this->openAiService->generateResponse($userInput);
            } catch (\Exception $e) {
                $responseText = 'Erreur de l\'API OpenAI : ' . $e->getMessage();
            }
        }

        return $this->render('chat/index.html.twig', [
            'form' => $form->createView(),
            'responseText' => $responseText,
        ]);
    }
}
