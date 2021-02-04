<?php

declare(strict_types=1);

namespace FXBO\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FXBO\DTO\RateUpdate;
use FXBO\Entity\Rate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use FXBO\DTO\RateListFilter;
use FXBO\Helpers\Pagination;
use FXBO\Repository\RateRepository;
use OpenApi\Annotations as OA;

/**
 * @Route(name="rate_")
 */
final class RateController extends AbstractController
{
    /**
     * @OA\Get(
     *     path="/rate",
     *     summary="Rate list",
     *     tags={"rate"},
     *     @OA\Parameter(ref="#/components/parameters/PageQuery"),
     *     @OA\Parameter(ref="#/components/parameters/LimitQuery"),
     *     @OA\Parameter(ref="#/components/parameters/RateFromQuery"),
     *     @OA\Parameter(ref="#/components/parameters/RateToQuery"),
     *     @OA\Parameter(ref="#/components/parameters/RateSortQuery"),
     *     @OA\Parameter(ref="#/components/parameters/RateOrderQuery"),
     *     @OA\Response(
     *         response="200",
     *         description="Rates",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="count",ref="#/components/schemas/PaginationResponse/properties/count"),
     *             @OA\Property(property="limit",ref="#/components/schemas/PaginationResponse/properties/limit"),
     *             @OA\Property(property="pages",ref="#/components/schemas/PaginationResponse/properties/pages"),
     *             @OA\Property(property="page",ref="#/components/schemas/PaginationResponse/properties/page"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Rate")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="default",
     *         ref="#/components/responses/error"
     *     ),
     *     security={{"api_key": {}}}
     * )
     *
     * @Route("/rate", name="list", methods={"GET"})
     */
    public function list(
        RateRepository $rateRepository,
        Pagination $pagination,
        Request $request,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator
    ): Response {
        /** @var RateListFilter $filter */
        $filter = $denormalizer->denormalize($request->query->all(), RateListFilter::class);
        $errors = $validator->validate($filter);
        if (0 !== count($errors)) {
            return $this->json(['error' => $errors], Response::HTTP_BAD_REQUEST);
        }
        return $this->json(
            $pagination->getPaginatedItems(
                $rateRepository->getListQuery($filter)
            )
        );
    }
    /**
     * @OA\Put(
     *     path="/rate/{id}",
     *     summary="Update rate",
     *     tags={"rate"},
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RateUpdate")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Rate",
     *         @OA\JsonContent(
     *             type="object",
     *             ref="#/components/schemas/Rate"
     *         )
     *     ),
     *     @OA\Response(
     *         response="default",
     *         ref="#/components/responses/error"
     *     ),
     *     security={{"api_key": {}}}
     * )
     *
     * @Route("/rate/{id<\d+>}", name="update", methods={"PUT"})
     */
    public function update(
        Rate $rate,
        Request $request,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var RateUpdate $update */
        $update = $denormalizer->denormalize($request->toArray(), RateUpdate::class);
        $errors = $validator->validate($update);
        if (0 !== count($errors)) {
            return $this->json(['error' => $errors], Response::HTTP_BAD_REQUEST);
        }
        $rate->setPrice($update->getPrice());
        $entityManager->persist($rate);
        $entityManager->flush();
        $entityManager->refresh($rate);
        return $this->json($rate);
    }
    /**
     * @OA\Delete (
     *     path="/rate/{id}",
     *     summary="Delete rate",
     *     tags={"rate"},
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Deleted successfully",
     *     ),
     *     @OA\Response(
     *         response="default",
     *         ref="#/components/responses/error"
     *     ),
     *     security={{"api_key": {}}}
     * )
     *
     * @Route("/rate/{id<\d+>}", name="delete", methods={"DELETE"})
     */
    public function delete(
        Rate $rate,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($rate);
        $entityManager->flush();
        return $this->json('');
    }
}
