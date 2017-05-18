<?php

namespace Codeages\Biz\Framework\Scheduler\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class ClearDeletedJobs extends AbstractJob
{
    public function execute()
    {
        $this->getSchedulerService()->clearJobs();
    }

    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}