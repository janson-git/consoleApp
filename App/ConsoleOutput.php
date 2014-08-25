<?php

class ConsoleOutput implements IOutput
{
    public function write($text)
    {
        fputs(STDERR, $text . PHP_EOL);
    }
}