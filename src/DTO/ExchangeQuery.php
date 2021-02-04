<?php

namespace FXBO\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 *
 * @OA\Parameter(
 *     parameter="ExchangeFrom",
 *     name="from",
 *     required=true,
 *     in="query",
 *     @OA\Schema(
 *         type="string",
 *         description="Currency code (ISO 4217)",
 *         example="USD"
 *     )
 * ),
 * @OA\Parameter(
 *     parameter="ExchangeTo",
 *     name="to",
 *     required=true,
 *     in="query",
 *     @OA\Schema(
 *         type="string",
 *         description="Currency code (ISO 4217)",
 *         example="EUR"
 *     )
 * ),
 * @OA\Parameter(
 *     parameter="ExchangeAmount",
 *     name="amount",
 *     required=true,
 *     in="query",
 *     @OA\Schema(
 *         type="number"
 *     )
 * )
 */
final class ExchangeQuery
{
    /**
     * @Assert\Length(max=3)
     */
    private string $from;

    /**
     * @Assert\Length(max=3)
     */
    private string $to;

    /**
     * @Assert\Positive
     */
    private string $amount;

    public function __construct(
        string $from,
        string $to,
        string $amount
    ) {
        $this->from = $from;
        $this->to = $to;
        $this->amount = $amount;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }
}
