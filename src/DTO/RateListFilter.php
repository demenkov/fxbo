<?php

namespace FXBO\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Parameter(
 *     parameter="RateFromQuery",
 *     name="from",
 *     required=false,
 *     in="query",
 *     @OA\Schema(
 *         type="string",
 *         format="date",
 *         example="1970-01-01",
 *     )
 * )
 * @OA\Parameter(
 *     parameter="RateToQuery",
 *     name="to",
 *     required=false,
 *     in="query",
 *     @OA\Schema(
 *         type="string",
 *         format="date",
 *         example="2030-01-01",
 *     )
 * )
 * @OA\Parameter(
 *     parameter="RateSortQuery",
 *     name="sort",
 *     required=false,
 *     in="query",
 *     @OA\Schema(
 *         type="string",
 *         enum={"id", "date", "base", "quote", "price", "provider", "created", "updated"},
 *         example="date",
 *     )
 * )
 * @OA\Parameter(
 *     parameter="RateOrderQuery",
 *     name="order",
 *     required=false,
 *     in="query",
 *     @OA\Schema(
 *         type="string",
 *         enum={"asc", "desc"},
 *         example="desc",
 *     )
 * )
 */
final class RateListFilter
{
    /**
     * @Assert\Date
     */
    private ?string $from;

    /**
     * @Assert\Date
     */
    private ?string $to;

    /**
     * @Assert\NotBlank
     * @Assert\Choice({"date", "id", "base", "quote", "price", "provider", "created", "updated"})
     */
    private string $sort;

    /**
     * @Assert\NotBlank
     * @Assert\Choice({"asc", "desc"})
     */
    private string $order;

    /**
     * @Assert\Length(max=10)
     */
    private ?string $provider;

    /**
     * @Assert\Length(max=3)
     */
    private ?string $base;

    /**
     * @Assert\Length(max=3)
     */
    private ?string $quote;

    public function __construct(
        string $sort = 'id',
        string $order = 'desc',
        ?string $from = null,
        ?string $to = null,
        ?string $provider = null,
        ?string $base = null,
        ?string $quote = null
    ) {
        $this->from = $from;
        $this->to = $to;
        $this->sort = $sort;
        $this->order = $order;
        $this->provider = $provider;
        $this->base = $base;
        $this->quote = $quote;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function getSort(): string
    {
        return $this->sort;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function getBase(): ?string
    {
        return $this->base;
    }

    public function getQuote(): ?string
    {
        return $this->quote;
    }
}
