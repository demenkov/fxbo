<?php

namespace FXBO\Model\Exchange;

use DateTime;
use DateTimeInterface;
use Exchanger\Contract\CurrencyPair as CurrencyPairContract;
use Exchanger\Contract\ExchangeRate;
use Exchanger\Contract\ExchangeRate as ExchangeRateContract;
use Exchanger\Contract\ExchangeRateQuery as ExchangeRateQueryContract;
use Exchanger\Contract\ExchangeRateService;
use Exchanger\Contract\HistoricalExchangeRateQuery as HistoricalExchangeRateQueryContract;
use Exchanger\CurrencyPair;
use Exchanger\ExchangeRateQuery;
use Exchanger\HistoricalExchangeRateQuery;
use Exchanger\Service\SupportsHistoricalQueries;
use Fisharebest\Algorithm\Dijkstra;
use FXBO\Entity\Rate;
use FXBO\Repository\RateRepository;
use LogicException;

class CrossExchange implements ExchangeRateService
{
    use SupportsHistoricalQueries;
    private RateRepository $rateRepository;

    public function __construct(
        RateRepository $rateRepository
    ) {
        $this->rateRepository = $rateRepository;
    }

    public function latest(
        CurrencyPair $currencyPair,
        array $options = []
    ): ExchangeRateContract {
        return $this->quote($currencyPair, null, $options);
    }

    public function quote(
        CurrencyPair $currencyPair,
        DateTimeInterface $date = null,
        array $options = []
    ): ExchangeRateContract {
        if ($currencyPair->isIdentical()) {
            return new CrossRate(
                1,
                $date ?? new DateTime('now'),
                $currencyPair
            );
        }
        $query = $this->getQuery($currencyPair, $date, $options);
        if ($this->supportQuery($query)) {
            return $this->getExchangeRate($query);
        }
        throw new LogicException('Unsupported pair');
    }

    protected function getQuery(
        CurrencyPair $currencyPair,
        DateTimeInterface $date = null,
        array $options = []
    ): ExchangeRateQueryContract {
        if (!is_null($date)) {
            return new HistoricalExchangeRateQuery($currencyPair, $date, $options);
        }

        return new ExchangeRateQuery($currencyPair, $options);
    }

    public function getExchangeRate(ExchangeRateQueryContract $exchangeQuery): ExchangeRate
    {
        $pair = $exchangeQuery->getCurrencyPair();
        $dateTime = $exchangeQuery instanceof HistoricalExchangeRateQuery ?
            $exchangeQuery->getDate() : null;
        return $this->findOnDateOneByCurrencyPair($pair, $dateTime);
    }

    public function supportQuery(ExchangeRateQueryContract $exchangeQuery): bool
    {
        $pair = $exchangeQuery->getCurrencyPair();
        if ($exchangeQuery instanceof HistoricalExchangeRateQuery) {
            $pairs = $this->rateRepository->getPairs($exchangeQuery->getDate()->format('Y-m-d'));
        } else {
            $pairs = $this->rateRepository->getPairs();
        }
        $find = [$pair->getBaseCurrency(), $pair->getQuoteCurrency()];
        if (in_array($find, $pairs, true) ||
            in_array(array_reverse($find), $pairs, true)) {
            return true;
        }
        $graph = $this->getGraph($pairs);
        $algorithm = new Dijkstra($graph);
        return 0 !== count($algorithm->shortestPaths($pair->getBaseCurrency(), $pair->getQuoteCurrency()));
    }

    protected function getLatestExchangeRate(\Exchanger\Contract\ExchangeRateQuery $exchangeQuery): ExchangeRate
    {
        return $this->findOnDateOneByCurrencyPair($exchangeQuery->getCurrencyPair(), new DateTime('now'));
    }

    protected function getHistoricalExchangeRate(HistoricalExchangeRateQueryContract $exchangeQuery): ExchangeRate
    {
        return $this->findOnDateOneByCurrencyPair($exchangeQuery->getCurrencyPair(), $exchangeQuery->getDate());
    }

    public function getGraph(array $pairs): array
    {
        $graph = [];
        foreach ($pairs as $pair) {
            [$base, $quote] = $pair;
            //weights should be based on to providers
            $graph[$base][$quote] = true;
            $graph[$quote][$base] = true;
        }
        return $graph;
    }

    public function getName(): string
    {
        return 'fxbo';
    }

    public function findOnDateOneByCurrencyPair(
        CurrencyPairContract $pair,
        DateTimeInterface $date = null,
    ): ?CrossRate {
        $rate = $this->rateRepository->getRate($pair, $date);
        //direct
        if (!is_null($rate)) {
            return $this->getQuote($pair, $rate);
        }
        //cross
        if (!is_null($date)) {
            $pairs = $this->rateRepository->getPairs($date->format('Y-m-d'));
        } else {
            $pairs = $this->rateRepository->getPairs();
        }
        $graph = $this->getGraph($pairs);
        $algorithm = new Dijkstra($graph);
        $path = $algorithm->shortestPaths($pair->getBaseCurrency(), $pair->getQuoteCurrency());
        $chain = array_shift($path);
        $i = 1;
        $pair = new CurrencyPair($chain[0], $chain[$i]);
        $rate = $this->rateRepository->getRate($pair, $date);
        $quote = $this->getQuote($pair, $rate);
        $value = $quote->getValue();
        //dump($quote, $value);
        do {
            $pair = new CurrencyPair($chain[$i], $chain[$i+1]);
            /** @var Rate $nextRate */
            $nextRate = $this->rateRepository->getRate($pair, $date);
            $nextQuote = $this->getQuote($pair, $nextRate);
            //direct cross quotation
            if ($quote->getCurrencyPair()->getBaseCurrency() === $nextQuote->getCurrencyPair()->getBaseCurrency() ||
                $quote->getCurrencyPair()->getQuoteCurrency() === $nextQuote->getCurrencyPair()->getQuoteCurrency()) {
                $value *= $nextQuote->getValue();
            }
            //reverse cross quotation
            if ($quote->getCurrencyPair()->getBaseCurrency() === $nextQuote->getCurrencyPair()->getQuoteCurrency() ||
                $quote->getCurrencyPair()->getQuoteCurrency() === $nextQuote->getCurrencyPair()->getBaseCurrency()) {
                $value /= $nextQuote->getValue();
            }
            $quote = $nextQuote;
            //dump($quote, $value);
            ++$i;
        } while (count($chain)-1 > $i);
        return new CrossRate(
            $value,
            $rate->getDate(),
            new CurrencyPair($chain[array_key_first($chain)], $chain[array_key_last($chain)])
        );
    }

    public function getQuote(
        CurrencyPairContract $pair,
        Rate $rate
    ): CrossRate {
        if ($rate->getBaseCurrency() === $pair->getBaseCurrency()) {
            return new CrossRate(
                $rate->getValue(),
                $rate->getDate(),
                $pair
            );
        }
        return new CrossRate(
            1 / $rate->getValue(),
            $rate->getDate(),
            new CurrencyPair($pair->getQuoteCurrency(), $pair->getBaseCurrency())
        );
    }
}
