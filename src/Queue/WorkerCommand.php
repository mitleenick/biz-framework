<?php

namespace Codeages\Biz\Framework\Queue;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Codeages\Biz\Framework\Queue\Worker;

class WorkerCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('queue:work')
            ->setDescription('Start processing jobs on the queue')
            ->addArgument('name', InputArgument::OPTIONAL, 'Queue name')
            ->addOption('once', null, InputOption::VALUE_NONE, 'Only process the next job on the queue')
            ->addOption('stop-when-idle', null, InputOption::VALUE_NONE, 'Worker stop when no jobs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueName = $input->getArgument('name') ?: 'default';

        $queue = $this->biz['queue.'.$queueName];

        $options = array(
            'once' => $input->getOption('once'),
            'stop_when_idle' => $input->getOption('stop-when-idle'),
        );

        $worker = new Worker($queue, $this->biz['queue.failer'], $options);
        $worker->run();
    }
}