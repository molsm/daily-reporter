<?php

namespace App\Sections;

use DailyReporter\Helper\Jira;
use DailyReporter\Helper\Time;
use DailyReporter\Jira\Client;
use DailyReporter\Sections\AbstractSection;
use Psr\Container\ContainerInterface;

class ListOfTodayDoneTickets extends AbstractSection
{
    /**
     * @var string
     */
    protected $sectionName = 'Done Today';

    /**
     * @var Client
     */
    private $client;

    public function __construct(ContainerInterface $container, Client $client)
    {
        parent::__construct($container);
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function process(): array
    {
        $data = [];

        $apiResult = $this->client->getWorklog(
            getenv('JIRA_WORKLOG_USERNAME'),
            getenv('REPORT_DATE_FROM'),
            getenv('REPORT_DATE_TO')
        );

        $totalTimeSpentInSeconds = 0;
        foreach ($apiResult->getResult() as $worklog) {
            $data[] = [
                'ticketId' => $worklog['issue']['key'],
                'ticketName' => $worklog['issue']['summary'],
                'timeSpent' => Time::convertSecondsIntoStringWithHour($worklog['timeSpentSeconds']),
                'comment' => $worklog['comment'],
                'ticketUrl' => Jira::getTicketUrl($worklog['issue']['key'])
            ];

            $totalTimeSpentInSeconds += $worklog['timeSpentSeconds'];
        }

        $totalTimeSpent = Time::convertSecondsIntoStringWithHour($totalTimeSpentInSeconds);
        $this->showDataResult($data, $totalTimeSpent);

        return ['doneTicketsList' => $data, 'totalTimeSpent' => $totalTimeSpent];
    }

    /**
     * @param array $data
     * @param string $totalTimeSpent
     * @return void
     */
    private function showDataResult(array $data, string $totalTimeSpent)
    {
        $data[] = ['', 'Total', $totalTimeSpent, '', ''];
        $this->io->table(
            ['Jira Ticket Id', 'Ticket Name', 'Time spent', 'Comment', 'Ticket Url'],
            $data
        );
    }
}