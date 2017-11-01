<?php

namespace App\Sections;

use DailyReporter\Exception\CanNotRetrieveDataFromJira;
use DailyReporter\Helper\Jira;
use DailyReporter\Helper\Time;
use DailyReporter\Sections\AbstractSection as Section;
use DailyReporter\Validator\JiraTicket as JiraTicketValidator;

class PendingTasks extends Section
{
    /**
     * @var string
     */
    protected $sectionName = 'Pending tasks';

    /**
     * @return array
     */
    public function process(): array
    {
        $pendingTime = 0;
        $continue = true;

        if ($this->io->confirm('Fill pending tickets?', false)) {
            while ($continue) {
                $ticketId = $this->io->ask('Provide ticket Id / Key:', null, new JiraTicketValidator);

                try {
                    $ticket = $this->client->getTicket($ticketId);
                } catch (CanNotRetrieveDataFromJira $e) {
                    $this->io->warning($e->getMessage());
                    continue;
                }

                $this->data[] = [
                    'ticketId' => $ticket['key'],
                    'ticketName' => $ticket['fields']['summary'],
                    'ticketUrl' => Jira::getTicketUrl($ticket['key'])
                ];

                $pendingTime += Jira::getTicketPendingTimeInSeconds(
                    $ticket['fields']['timetracking']['originalEstimateSeconds'],
                    $ticket['fields']['timetracking']['timeSpentSeconds']
                );

                $this->io->title('Current pending tickets');
                $this->showData($this->data);

                $continue = $this->io->confirm('Add more?', true);
            }
        }

        return ['pendingTasks' => $this->data, 'pendingTime' => Time::convertSecondsIntoStringWithHour($pendingTime)];
    }

    /**
     * @param array $data
     */
    private function showData(array $data)
    {
        $this->io->table(
            ['Ticket Id', 'Ticket name', 'Ticket URL'],
            $data
        );
    }
}