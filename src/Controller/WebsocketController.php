<?php

namespace App\Controller;

use App\Form\WebsocketConnectionType;
use App\Form\WebsocketMessageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



class WebsocketController extends AbstractController
{
    #[Route('/websocket')]
    public function test(Request $request): Response
    {
        $websocketForm = $this->createForm(WebsocketMessageType::class);
//        $websocketForm->handleRequest($request);
//        if($websocketForm->isSubmitted() && $websocketForm->isValid()){
//            $data = $websocketForm->getData();
//            $ipAddress = $data->getIpAddress();
//            $port = $data->getPort();
//
//            $this->addFlash('success', 'IP Address: ' . $ipAddress . ' Port: ' . $port);
//        }


        return $this->render('websockets.html.twig', [
            'form' => $websocketForm->createView(),
            'websocketUrl' => $this->getParameter('websocket_url')
        ]);
    }
}
