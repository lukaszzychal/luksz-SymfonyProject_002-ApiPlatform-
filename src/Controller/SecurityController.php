<?php

namespace App\Controller;

use ApiPlatform\Core\Api\IriConverterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function index(IriConverterInterface $iriConverterInterface): Response
    {

        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->json([
                'error' => 'Invalid login request: check that the Content-Type header is "application/json".'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new Response(null, Response::HTTP_NO_CONTENT, [
            'Location' => $iriConverterInterface->getIriFromItem($this->getUser())
        ]);

        // return $this->json([
        //     'user' => $this->getUser() ? $this->getUser()->getId() : null
        // ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('should not be reached');
    }
}
