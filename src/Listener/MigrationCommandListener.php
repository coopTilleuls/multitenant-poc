<?php

namespace App\Listener;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsEventListener]
class MigrationCommandListener
{
    public function __construct(private KernelInterface $kernel)
    {
    }

    public function __invoke(ConsoleTerminateEvent $event): void
    {
        if ('doctrine:migrations:migrate' == $event->getCommand()->getName() && 0 == $event->getExitCode()) {
            $application = new Application($this->kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput([
                'command' => 'app:database:foreigns',
            ]);

            $application->run($input, new NullOutput());
        }
    }
}
