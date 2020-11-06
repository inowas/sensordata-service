<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SensorDataController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"})
     * @return JsonResponse
     */
    public function sensorList(): Response
    {
        return new JsonResponse([], Response::HTTP_OK);
    }

    /**
     * @Route("/{id}", methods={"GET"})
     * @param string $id
     * @return JsonResponse
     */
    public function sensorMetaData(string $id): Response
    {
        return new JsonResponse([], Response::HTTP_OK);
    }

    /**
     * @Route("/{id}/data", methods={"GET"})
     * @param string $id
     * @return JsonResponse
     */
    public function sensorData(string $id, Request $request): Response
    {
        return new JsonResponse(['data' => 123]);
    }

    /**
     * @Route("/{id}/data", methods={"POST"})
     * @param string $id
     * @return JsonResponse
     */
    public function addSensorData(string $id, Request $request): Response
    {
        return new JsonResponse(['data' => 123]);
    }
}
