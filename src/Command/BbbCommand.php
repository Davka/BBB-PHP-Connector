<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\HttpClient\HttpClient;
use SimpleXMLElement;

class BbbCommand extends Command
{
    protected static $defaultName = 'bbb:get-meetings';
    private          $apiEndpoint;
    private          $apiSecret   = '';

    protected function configure()
    {
        $this->apiEndpoint = $_ENV['BBB_API_ENDPOINT'];
        $this->apiSecret   = $_ENV['BBB_API_SECRET'];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client          = HttpClient::create();
        $queryBuild      = $this->getQueryBuild();
        $url             = $this->getUrl($queryBuild);
        $response        = $client->request('POST', $url);
        $meetings        = new SimpleXMLElement($response->getContent());
        $completeCounter = count($meetings->meetings->meeting);
        $table           = new Table($output);
        $table->setHeaders(
            [
                'Meeting-ID',
                'Meeting-Name',
                '# Teilnehmer',
                '# Webcams',
                '# Zuhörer',
                '# Audio',
                '# Moderatoren'
            ]);
        foreach ($meetings->meetings->meeting as $meeting) {
            $table->addRow(
                [
                    $meeting->meetingID,
                    $meeting->meetingName,
                    $meeting->participantCount,
                    $meeting->videoCount,
                    $meeting->listenerCount,
                    $meeting->voiceParticipantCount,
                    $meeting->moderatorCount,
                ]
            );
        }

        $output->writeln("<info>{$completeCounter} Konferenzen</info>");
        $table->render();
        return 0;
    }

    private function getQueryBuild(array $parameters = []): string
    {
        return http_build_query($parameters);
    }

    private function getChecksum(string $route, string $queryBuild): string
    {
        return sha1($route . $queryBuild . $this->apiSecret);
    }

    private function getUrl(string $queryBuild, string $apiRoute = "getMeetings"): string
    {
        $checksum = $this->getChecksum($apiRoute, $queryBuild);
        return $this->apiEndpoint . "/{$apiRoute}?checksum={$checksum}";
    }
}
