<?php

class CommandOption
{
    protected $name;
    protected $alias;
    protected $isParametric;
    
    protected $description = '';
    
    public function __construct($name, $alias = null, $isParametric = false)
    {
        $this->name = $name;
        if (!is_null($alias)) {
            $this->alias = $alias;
        }
        $this->isParametric = (bool) $isParametric;
    }
    
    public function setDescription($text)
    {
        $this->description = $text;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getAlias()
    {
        return $this->alias;
    }
    
    public function isParametric()
    {
        return $this->isParametric;
    }
} 