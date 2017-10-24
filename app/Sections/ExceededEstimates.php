<?php

namespace App\Sections;

use DailyReporter\Sections\AbstractSection as Section;

class ExceededEstimates extends Section
{
    /**
     * @var string
     */
    protected $sectionName = 'Exceeded estimates';

    /**
     * @return array
     */
    public function process(): array
    {
        $data = [];

        $data[] = $this->io->ask('Write exceeded estimate', 'n/a');

        return ['exceededEstimates' => $data];
    }
}