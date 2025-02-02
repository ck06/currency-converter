<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CurrencyImporter;
use http\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:currency:import', description: 'Update currency data based on third party json file')]
class CurrencyImportTask extends Command
{
    public function __construct(private HttpClientInterface $scraper, private CurrencyImporter $importer)
    {
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

        $this->process($code, $input->getOption('to'));
        $this->processDefault($code);

        return self::SUCCESS;
    }

    private function getUrl(string $code): string
    {
        return sprintf('https://www.floatrates.com/daily/%s.json', strtolower($code));
    }

    private function getData(string $code): string
    {
        $response = $this->scraper->request('GET', $this->getUrl($code));
        if ($response->getStatusCode() !== 200) {
            throw new NotFoundHttpException('Unable to import - target URL unavailable');
        }

        return $response->getContent();
    }

    private function process(string $code, ?string $target): void
    {
        $content = $this->getData($code);
        if ($target !== null) {
            $this->importer->importOne($content, $target);
        } else {
            $this->importer->importAll($content);
        }
    }

    private function processDefault(string $target): void
    {
        $code = $target === 'EUR' ? 'USD' : 'EUR';
        $content = $this->getData($code);
        $this->importer->createDefault($content, $target);
    }
}
