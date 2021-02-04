<?php

declare(strict_types=1);

namespace FXBO\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class OptionsController extends AbstractController
{
    /**
     * @OA\Options(
     *     path="/",
     *     description="CORS options callback",
     *     @OA\Response(
     *          response="200",
     *          description="CORS headers",
     *     ),
     * )
     *
     * @Route("/", name="options", methods={"OPTIONS"})
     * @Route("/{any<.+>}", name="options", methods={"OPTIONS"})
     *
     * @return Response
     */
    public function options(): Response
    {
        return new Response();
    }
}
