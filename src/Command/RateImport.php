<?php

declare(strict_types=1);

namespace FXBO\Command;

use FXBO\Parser\CoinDeskParser;
use FXBO\Parser\EcbParser;
use FXBO\Parser\ParserInterface;
use FXBO\Repository\RateRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RateImport extends Command
{
    public LoggerInterface $logger;
    public RateRepository $rateRepository;
    public CoinDeskParser $coinDeskParser;
    public EcbParser $ecbParser;

    public function __construct(
        LoggerInterface $logger,
        RateRepository $rateRepository,
        CoinDeskParser $coinDeskParser,
        EcbParser $ecbParser,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->logger = $logger;
        $this->rateRepository = $rateRepository;
        $this->coinDeskParser = $coinDeskParser;
        $this->ecbParser = $ecbParser;
    }
    protected function configure(): void
    {
        $this->setName('rate:import');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $rates = [];
        foreach (get_object_vars($this) as $property => $value) {
            if (!$value instanceof ParserInterface) {
                continue;
            }
            foreach ($this->{$property}->parse() as $item) {
                $rates[] = $item;
            }
        }
        $this->rateRepository->bulkUpsert($rates);
        $this->logger->info(sprintf('Imported %d quotes', count($rates)));
        return 0;
    }
}
