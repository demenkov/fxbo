<?php

declare(strict_types=1);

namespace FXBO\Helpers;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\RequestStack;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Page",
 *     description="Page number",
 *     type="integer",
 *     example=1,
 * )
 * @OA\Schema(
 *     schema="Limit",
 *     description="Items per page",
 *     type="integer",
 *     example=20,
 * )
 * @OA\Parameter(
 *     parameter="PageQuery",
 *     name="page",
 *     required=false,
 *     in="query",
 *     @OA\Schema(
 *        ref="#/components/schemas/Page"
 *     )
 * )
 * @OA\Parameter(
 *     parameter="LimitQuery",
 *     name="limit",
 *     required=false,
 *     in="query",
 *     @OA\Schema(
 *        ref="#/components/schemas/Limit"
 *     )
 * )
 * @OA\Schema(
 *     schema="PaginationResponse",
 *     @OA\Property(
 *         property="count",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="limit",
 *         type="integer",
 *         example="20"
 *     ),
 *     @OA\Property(
 *         property="pages",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="page",
 *         type="integer",
 *         example="1"
 *     ),
 * )
 */
final class Pagination
{
    private const PAGE_FIRST = 1;
    private const PAGE_LIMIT = 20;
    private int $page = self::PAGE_FIRST;
    private int $limit = self::PAGE_LIMIT;

    public function __construct(
        RequestStack $requestStack
    ) {
        if ($request = $requestStack->getCurrentRequest()) {
            $this->page = max((int) $request->get('page', $this->page), $this->page);
            $this->limit = max((int) $request->get('limit', $this->limit), $this->limit);
        }
    }

    public function paginate(Query $dql): Paginator
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($this->limit * ($this->page - 1)) // Offset
            ->setMaxResults($this->limit); // Limit

        return $paginator;
    }

    public function getPaginatedItems(Query $dql): array
    {
        $paginator = $this->paginate($dql);

        return [
            'count' => $paginator->count(),
            'pages' => ceil($paginator->count() / $this->limit),
            'limit' => $this->limit,
            'page' => $this->page,
            'items' => $paginator->getIterator(),
        ];
    }
}
