<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CurrencyImporter;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

#[AsCommand(name: 'app:currency:import', description: 'Update currency data based on third party json file')]
class CurrencyImportTask extends Command
{
    public function __construct(
        private HttpClientInterface $scraper,
        private CurrencyImporter $importer,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'from',
            null,
            InputOption::VALUE_REQUIRED,
            'Allows you to override which feed to use',
            'EUR',
        );
        $this->addOption(
            'to',
            null,
            InputOption::VALUE_REQUIRED,
            'If given, lets you specify which currency to update',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $code = $input->getOption('from');
        $to = $input->getOption('to');

        $this->logger->info(sprintf(
            '[%s] Importing for %s %s',
            $this->getName(),
            $code,
            $to === null ? '' : sprintf('(only importing %s)', $to)
        ));

        try {
            $this->process($code, $to);
            $this->processDefault($code);
        } catch (Throwable $e) {
            $this->logger->error(sprintf(
                '[%s] An error occurred while executing the task: %s',
                $this->getName(),
                $e->getMessage(),
            ));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function getUrl(string $code): string
    {
        return sprintf('https://www.floatrates.com/daily/%s.json', strtolower($code));
    }

    private function getData(string $code): array
    {
        $response = $this->scraper->request('GET', $this->getUrl($code));
        if ($response->getStatusCode() !== 200) {
            $this->logger->warning(sprintf('[%s] Feed for currency %s is not available', $this->getName(), $code));
            throw new NotFoundHttpException('Unable to import - target URL unavailable');
        }

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function process(string $code, ?string $target): void
    {
        $json = $this->getData($code);
        if ($target !== null) {
            $this->importer->importOne($json, $target);
        } else {
            $this->importer->importAll($json);
        }
    }

    private function processDefault(string $target): void
    {
        $code = $target === 'EUR' ? 'USD' : 'EUR';
        $json = $this->getData($code);
        $this->importer->importDefault($json, $target);
    }
}
