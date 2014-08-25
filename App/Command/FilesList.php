<?php

namespace Command;

class FilesList extends Command
{
    protected function configure()
    {
        $this->setName('list') // this is the name for command. Used on command calls.
            ->setDescription('Show list of files in directory')
            ->addParametricOption('f', 'folder', 'set path to folder. For example: $ php index.php list -f path/to/folder');
    }
    
    public function execute()
    {
        // if no option 'f' given
        $path = $this->getOptionValue('f');
        if ( is_null($path) || !is_null($this->getOptionValue('h'))) {
            $this->output($this->getHelp());
            exit(1);
        }
        
        $dirPath = realpath(ROOT_DIR . DIRECTORY_SEPARATOR . $path);
        // Deny view directories not in ROOT_DIR
        if (mb_strlen($dirPath) < mb_strlen(ROOT_DIR)) {
            throw new \Exception("You are not allowed to view this folder");
        }

        // check for existing and readable
        if (!file_exists($dirPath)) {
            throw new \Exception("Directory {$path} not exists");
        }
        if (!is_readable($dirPath)) {
            throw new \Exception("Directory {$path} not readable. Check for permissions.");
        }
        if (!is_dir($dirPath)) {
            throw new \Exception("{$path} is not directory");
        }
        // get list
        $list = scandir($dirPath);
        $list = array_filter($list, function($item) {
                return !in_array($item, array('.', '..'));
            });
        // display list
        $this->output($list);
    }
}