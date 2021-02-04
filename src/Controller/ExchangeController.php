<?php

declare(strict_types=1);

namespace FXBO\Controller;

use Exchanger\CurrencyPair;
use FXBO\DTO\ExchangeQuery;
use FXBO\DTO\RateUpdate;
use FXBO\Model\Exchange\CrossExchange;
use FXBO\Repository\RateRepository;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Brick\Math\BigDecimal;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ExchangeController extends AbstractController
{
    /**
     * @Route("/exchange", name="exchange", methods={"GET"})
     *
     * @OA\Get(
     *     path="/exchange",
     *     summary="Exchange",
     *     tags={"exchange"},
     *     @OA\Parameter(ref="#/components/parameters/ExchangeFrom"),
     *     @OA\Parameter(ref="#/components/parameters/ExchangeTo"),
     *     @OA\Parameter(ref="#/components/parameters/ExchangeAmount"),
     *     @OA\Response(
     *         response="200",
     *         description="Converted amount",
     *         @OA\JsonContent(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response="default",
     *         ref="#/components/responses/error"
     *     )
     * )
     *
     * @return Response
     */
    public function exchange(
        Request $request,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        CrossExchange $exchange
    ): Response {
        try {
            /** @var ExchangeQuery $query */
            $query = $denormalizer->denormalize($request->query->all(), ExchangeQuery::class);
        } catch (MissingConstructorArgumentsException $exception) {
            return $this->json(['message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        $errors = $validator->validate($query);
        if (0 !== count($errors)) {
            return $this->json(['error' => $errors], Response::HTTP_BAD_REQUEST);
        }
        try {
            $rate = $exchange->quote(new CurrencyPair($query->getFrom(), $query->getTo()));
        } catch (LogicException $exception) {
            return $this->json(['message' =>$exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        $result = round(
            BigDecimal::of($rate->getValue())->multipliedBy($query->getAmount())->toFloat(),
            3,
            PHP_ROUND_HALF_DOWN
        );

        return $this->json($result);
    }
}
