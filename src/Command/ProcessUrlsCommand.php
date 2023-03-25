<?php

declare(strict_types=1);

namespace Doomy\BatchUrlReader\Command;

use Doomy\BatchUrlReader\Exception\InvalidUrlsDataException;
use Doomy\BatchUrlReader\Exception\UrlsFileNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessUrlsCommand extends Command
{
    const ARG_URLS_FILE_PATH='urls-file-path';

    const URLS_PARSED_FILENAME = 'urlData.json';

    protected function configure()
    {
        $this->addArgument(self::ARG_URLS_FILE_PATH, InputArgument::REQUIRED);
    }

    /**
     * @throws UrlsFileNotFoundException
     * @throws InvalidUrlsDataException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $urls = $this->getUrls($input);

        $outputData = [];

        foreach ($urls as $url) {
            if (empty($url)) {
                continue;
            }

            $rawData = @file_get_contents($url);
            if ($rawData === false) {
                $output->write('<fg=red>x</>');
                continue;
            }

            $outputData[$url] = $rawData;
            $output->write('<fg=green>✓</>');
        }

        file_put_contents(self::URLS_PARSED_FILENAME, json_encode($outputData));
        $output->writeln("\n\nYour data is ready at: " . self::URLS_PARSED_FILENAME);

        return 1;
    }

    /**
     * @return string[]
     */
    private function getUrls(InputInterface $input): array
    {
        $fileName = $input->getArgument(self::ARG_URLS_FILE_PATH);

        if (!$urlsData = @file_get_contents($fileName)) {
            throw new UrlsFileNotFoundException();
        }

        $urls = explode("\n", $urlsData);
        if (count($urls) === 0) {
            throw new InvalidUrlsDataException();
        }

        return $urls;
    }

}