<?php

declare(strict_types=1);

namespace FXBO\Entity;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Exchanger\Contract\CurrencyPair;
use Exchanger\Contract\ExchangeRate;
use OpenApi\Annotations as OA;

/**
 *
 * @OA\Schema(
 *         schema="Rate",
 *         @OA\Property(
 *            property="id",
 *            type="integer",
 *            example=1
 *        ),
 *        @OA\Property(
 *           property="date",
 *           type="string",
 *           example="2021-01-01"
 *        ),
 *        @OA\Property(
 *            property="base",
 *            type="string",
 *            example="USD"
 *        ),
 *        @OA\Property(
 *            property="quote",
 *            type="string",
 *            example="BTC"
 *        ),
 *        @OA\Property(
 *            property="price",
 *            type="string",
 *            example="10.45"
 *        ),
 *        @OA\Property(
 *            property="provider",
 *            type="string",
 *            example="ecb"
 *        ),
 *        @OA\Property(
 *            property="created",
 *            type="string",
 *            example="2021-01-01 00:00:00"
 *        ),
 *        @OA\Property(
 *            property="updated",
 *            type="string",
 *            example="2022-01-01 00:00:00"
 *        ),
 *     ),
 * )
 *
 * @ORM\Table(
 *     name="rate",
 *     uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="rate_date_base_quote_provider_uindex",
 *         columns={"date", "base", "quote", "provider"}
 *     )},
 *     indexes={
 *     @ORM\Index(
 *         name="rate_quote_index",
 *         columns={"quote"}
 *     ),
 *     @ORM\Index(
 *         name="rate_base_index",
 *         columns={"base"}
 *     )}
 * )
 * @ORM\Entity
 */
class Rate implements ExchangeRate, CurrencyPair
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\Column(name="date", type="datetime_immutable", nullable=false)
     */
    private DateTimeImmutable $date;

    /**
     * @ORM\Column(name="base", type="string", length=3, nullable=false)
     */
    private string $base;

    /**
     * @ORM\Column(name="quote", type="string", length=3, nullable=false)
     */
    private string $quote;

    /**
     * @ORM\Column(name="price", type="decimal", precision=20, scale=6, nullable=false)
     */
    private string $price;

    /**
     * @ORM\Column(name="provider", type="string", length=10, nullable=false)
     */
    private string $provider;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private DateTime $created;

    /**
     * @ORM\Column(name="updated", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private DateTime $updated;

    public function __construct(
        DateTimeImmutable $date,
        string $base,
        string $quote,
        string $price,
        string $provider,
    ) {
        $this->date = $date;
        $this->base = $base;
        $this->quote = $quote;
        $this->price = $price;
        $this->provider = $provider;
        $this->created = $this->updated = new DateTime('now');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setPrice(string $price): void
    {
        $this->price = $price;
    }

    public function getValue(): float
    {
        return (float) $this->price;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function getProviderName(): string
    {
        return $this->provider;
    }

    public function getCurrencyPair(): CurrencyPair
    {
        return $this;
    }

    public function getBaseCurrency(): string
    {
        return $this->base;
    }

    public function getQuoteCurrency(): string
    {
        return $this->quote;
    }

    public function isIdentical(): bool
    {
        return $this->base === $this->quote;
    }

    public function __toString(): string
    {
        return sprintf('%s/%s', $this->base, $this->quote);
    }
}
