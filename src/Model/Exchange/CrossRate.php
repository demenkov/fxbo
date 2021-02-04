<?php

namespace FXBO\Model\Exchange;

use DateTime;
use DateTimeInterface;
use Exchanger\Contract\CurrencyPair;
use Exchanger\Contract\ExchangeRate;

class CrossRate implements ExchangeRate
{
    private float $value;
    private DateTimeInterface $date;
    private CurrencyPair $currencyPair;

    public function __construct(
        float $value,
        DateTimeInterface $date,
        CurrencyPair $currencyPair,
    ) {
        $this->value = $value;
        $this->date = $date;
        $this->currencyPair = $currencyPair;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getDate(): DateTime
    {
        return new DateTime("@{$this->date->getTimestamp()}");
    }

    public function getProviderName(): string
    {
        return 'fxbo';
    }

    public function getCurrencyPair(): CurrencyPair
    {
        return $this->currencyPair;
    }
}
