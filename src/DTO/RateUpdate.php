<?php

namespace FXBO\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="RateUpdate",
 *     description="Rate update model",
 *     type="object",
 *     @OA\Property(property="price",ref="#/components/schemas/Rate/properties/price"),
 * )
 */
final class RateUpdate
{
    /**
     * @Assert\NotBlank()
     */
    private string $price;

    public function __construct(
        string $price
    ) {
        $this->price = $price;
    }

    public function getPrice(): string
    {
        return $this->price;
    }
}
