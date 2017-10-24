<?php

namespace App\Sections;

use DailyReporter\Exception\CanNotRetrieveDataFromJira;
use DailyReporter\Helper\Jira;
use DailyReporter\Jira\Client;
use DailyReporter\Sections\AbstractSection as Section;
use Psr\Container\ContainerInterface;
use DailyReporter\Validator\JiraTicket as JiraTicketValidator;

class PendingTasks extends Section
{
    /**
     * @var string
     */
    protected $sectionName = 'Pending tasks';

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

        if ($this->io->ask('Fill pending tickets?', false)) {
            while ($continue) {
                $ticketId = $this->io->ask('Provide ticket Id / Key', null, new JiraTicketValidator);

                try {
                    $ticket = $this->client->getTicket($ticketId);
                } catch (CanNotRetrieveDataFromJira $e) {
                    $this->io->warning($e->getMessage());
                    continue;
                }

                $data[] = [
                    'ticketId' => $ticket['key'],
                    'ticketName' => $ticket['fields']['summary'],
                    'ticketUrl' => Jira::getTicketUrl($ticket['key'])
                ];

                $this->io->title('Current pending tickets');
                $this->showData($data);

                $continue = $this->io->confirm('Add more?', true);
            }
        }

        return ['pendingTasks' => $data];
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