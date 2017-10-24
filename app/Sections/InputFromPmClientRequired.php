<?php

namespace App\Sections;

use DailyReporter\Sections\AbstractSection as Section;

class InputFromPmClientRequired extends Section
{
    /**
     * @var string
     */
    protected $sectionName = 'Input from PM / Client Required';

    /**
     * @return array
     */
    public function process(): array
    {
        $data = [];

        $data[] = $this->io->ask('Write text', 'n/a');

        return ['inputFromPmOrClient' => $data];
    }
}