<?php

class ConsoleApplication
{
    /** @var  \Command\Command[] */
    protected $commands;
    /** @var  IOutput */
    protected $output;
    
    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }
    
    public function addCommand(\Command\Command $command)
    {
        $command->setOutputHandler($this->output);
        $this->commands[$command->getName()] = $command;
    }
    
    public function run()
    {
        global $argv;
        // Don't change global $argv. Only copy it.
        $args = $argv;
        
        // firstParam - script name from command line
        array_shift($args);
        $commandName = array_shift($args);
        
        if (empty($commandName)) {
            $list = array_keys($this->commands);
            $this->output->write("Available commands: \n" . implode("\n", $list));
            exit;
        }

        // Possible incoming options as [X] => "--config=someValue"
        // change it to two values: [X] => "--config", [X+1] => "someValue"
        $argsFiltered = array();
        foreach ($args as $arg) {
            if (strpos($arg, '--') !== 0) {
                array_push($argsFiltered, $arg);
            } else {
                if (strpos($arg, '=') !== false) {
                    list($option, $value) = explode('=', $arg, 2);
                    array_push($argsFiltered, $option, $value);
                } else {
                    array_push($argsFiltered, $arg);
                }
            }
        }
        $args = $argsFiltered;

        
        try {
            $command = array_key_exists($commandName, $this->commands) ? $this->commands[$commandName] : null;
            if (is_null($command)) {
                throw new \Exception("Command {$commandName} not exists");
            }
            
            // prepare command options and run command
            $command->parse($args);
            
            $command->__before();
            $command->execute();
            $command->__after();
            
        } catch (\Exception $e) {
            $this->output->write("Exception: " . $e->getMessage());
        }
    }
}