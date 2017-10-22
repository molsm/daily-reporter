<?php

namespace DailyReporter\Sections;

use DailyReporter\Exception\CanNotRetrieveDataFromJira;
use DailyReporter\Helper\Jira;
use DailyReporter\Jira\Client;
use DailyReporter\Validator\JiraTicket as JiraTicketValidator;
use Psr\Container\ContainerInterface;

class SummaryOfCriticalIssues extends AbstractSection
{
    protected $sectionName = 'Summary of critical issue';

    /**
     * @var Client
     */
    private $client;

    /**
     * PendingTasks constructor.
     * @param ContainerInterface $container
     * @param Client $client
     */
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
        $continue = true;

        if ($this->io->confirm('Do you have critical issues?', false)) {
            while ($continue) {
                $ticketId = $this->io->ask('Enter Jira ticket Id', null, new JiraTicketValidator);

                try {
                    $ticket = $this->client->getTicket($ticketId);
                } catch (CanNotRetrieveDataFromJira $e) {
                    $this->io->warning($e->getMessage());
                    continue;
                }

                $ticketData = [
                    'ticketId' => $ticket['key'],
                    'ticketName' => $ticket['fields']['summary'],
                    'ticketUrl' => Jira::getTicketUrl($ticket['key'])
                ];

                $this->showData([$ticketData]);

                if ($this->io->confirm(sprintf('Would you like comment something for %s', $ticketId), false)) {
                    $ticketData['comment'] = $this->io->ask('Write your text', '');
                }

                $data[] = $ticketData;

                $continue = $this->io->confirm('Add one more?', false);
            }
        }

        return ['summaryOfCriticalIssues' => $data];
    }

    /**
     * @param array $data
     */
    private function showData(array $data)
    {
        $this->io->table(
            ['Ticket Id', 'Ticket name', 'Ticket URL', 'Comment'],
            $data
        );
    }
}