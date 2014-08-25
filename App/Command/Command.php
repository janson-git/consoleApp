<?php

namespace Command;

use IOutput;

abstract class Command
{
    protected $name;
    protected $description;
    
    protected $optionsDisabled = false;
    protected $previousOption;
    protected $currentOption;
    protected $currentOptionFullName;

    protected $aliases = array();
    /** @var \CommandOption[] */
    protected $allowedOptions = array();

    protected $options = array();
    protected $optionDescriptions = array();
    /** @var  IOutput */
    protected $outputHandler;
    
    const HELP_OPTION = 'h';
    const HELP_ALIAS = 'help';
    
    public function __construct()
    {
        $this->setName(strtolower(__CLASS__));
        // default 'help' param for all commands. It rewritable in subclass
        $this->addTriggerOption(self::HELP_OPTION, self::HELP_ALIAS, 'show this help info');
        $this->configure();
    }
    
    public function setOutputHandler(IOutput $output)
    {
        $this->outputHandler = $output;
    }
    
    
    public function __before()
    {
        // if options disabled but incoming options 'h' is given
        if ($this->optionsDisabled) { 
            if ($this->getOptionValue(self::HELP_OPTION) !== null) {
                $this->output($this->getHelp());
                exit(1);
            }
        }
        // if we have options parser enabled but no options given to command - show help text
        else {
            if ( (!empty($this->allowedOptions) && empty($this->options)) || !is_null($this->getOptionValue(self::HELP_OPTION))) {
                $this->output($this->getHelp());
                exit(1);
            }
        }
    }
    
    public function __after() {}
    
    abstract protected function configure();
    abstract public function execute();
    
    public function output($value)
    {
        if (!is_null($this->outputHandler)) {
            if (is_array($value)) {
                $value = print_r($value, true);
            }
            $this->outputHandler->write($value);
        }
    }
    
    protected function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    protected function setDescription($text)
    {
        $this->description = $text;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }


    /**
     * Add option not expected param value.
     * @param string $name Long option name, it used like '--config=someValue'
     * @param string $alias Short option name, it used like '-c someValue'
     * @param string $description Description of option
     * @return $this
     */
    protected function addTriggerOption($name, $alias = null, $description = null)
    {
        $option = new \CommandOption($name, $alias, false);
        $option->setDescription($description);
        
        $this->allowedOptions[$name] = $option;
        $this->updateAliases($name, $alias);
        
        return $this;
    }

    /**
     * Add option that is expected param value.
     * @param string $name Long option name, it used like '--config=someValue'
     * @param string $alias Short option name, it used like '-c someValue'
     * @param string $description Description of option
     * @return $this
     */
    protected function addParametricOption($name, $alias = null, $description = null)
    {
        $option = new \CommandOption($name, $alias, true);
        $option->setDescription($description);

        $this->allowedOptions[$name] = $option;
        $this->updateAliases($name, $alias);
        
        return $this;
    }

    protected function disableOptions()
    {
        $this->optionsDisabled = true;
        return $this;
    }
    
    /**
     * Inner method to update aliases array.
     * @param string $option
     * @param string $alias
     */
    protected function updateAliases($option, $alias)
    {
        if (!is_null($alias)) {
            $this->aliases[$alias] = $option;
        } else {
            if (false !== $key = array_search($option, $this->aliases)) {
                unset($this->aliases[$key]);
            }
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getOptionValue($name)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : null;
    }

    /**
     * Combine command manual by command description and command allowed options.
     * @return string
     */
    protected function getHelp()
    {
        $strings = array();
        if (!empty($this->description)) {
            $strings[] = $this->description;
        }
    
        $aliases = array_flip($this->aliases);

        foreach ($this->allowedOptions as $option) {
            $description = $option->getDescription();
            $optionName = $option->getName();
            
            $name = $optionName;
            if (array_key_exists($optionName, $aliases)) {
                $name .= ", --" . $aliases[$optionName];
            }
            $strings[] = "-{$name} - {$description}";
        }
        return implode(PHP_EOL, $strings);
    }
    
    
    public function parse($args)
    {
        $result = array();

        // Current state: wait for option (false) or for options data (true)
        $waitForData = false;

        foreach ($args as $arg) {
            $isOption = $this->isOption($arg);
            // when we need data, but option given - fail!
            if ($waitForData === true && $isOption === true) {
                throw new \Exception("Option '{$this->previousOption}' expects some value");
            }

            if ($isOption) {
                if ($this->allowedOptions[$this->currentOptionFullName]->isParametric()) {
                    $waitForData = true;
                } else {
                    $waitForData = false;
                    $result[$this->currentOptionFullName] = true;
                }
                continue;
            }

            if ($waitForData === false && !$isOption) {
                throw new \Exception("Option '{$this->currentOption}' not expect param");
            } elseif ($waitForData === true && !$isOption) {
                // after $this->isOption() we have full option name in $this->currentOption
                $result[$this->currentOptionFullName] = $arg;
                $waitForData = false;
            }
        }

        // for last: when we wait options data, but this is end
        if ($waitForData === true) {
            throw new \Exception("Option '{$this->currentOption}' expects some value");
        }
        
        $this->options = $result;
    }

    protected function isOption($arg)
    {
        if (preg_match("#\-(?<long>\-)?(?<option>[a-zA-Z\-]{1,})#", $arg, $matches)) {
            $isLong = $matches['long'];
            $option = $matches['option'];

            $fullName = $option;
            if ($isLong) {
                $fullName = array_key_exists($option, $this->aliases) ? $this->aliases[$option] : $option;
            }

            if (!array_key_exists($fullName, $this->allowedOptions)) {
                throw new \Exception("Unknown option {$arg}");
            }
            $this->previousOption = $this->currentOption;
            $this->currentOption = $option;
            $this->currentOptionFullName = $fullName;
            return true;
        }
        return false;
    }
}