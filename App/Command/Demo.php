<?php

namespace Command;

class Demo extends Command
{
    protected function configure()
    {
        // That means you want to use this Demo command class as 'demo' console command
        // with options '-c', '-p' (alias '--password'), '-h' (alias '--help')
        // looks like:
        // $ php index.php demons
        // $ php index.php demons -h
        $this->setName('demo')
            ->setDescription('Demo command shows example.')
            ->disableOptions();
    }
    
    public function execute()
    {
        $this->output('DEMO COMMAND');
    }
}