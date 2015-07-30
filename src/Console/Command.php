<?php

namespace LaravelDoctrine\ORM\Console;

class Command extends \Illuminate\Console\Command
{
    /**
     * Display blue message
     *
     * @param        $message
     * @param string $color
     */
    public function message($message, $color = 'blue')
    {
        $this->getOutput()->writeln('<fg=' . $color . '>' . $message . '</fg=' . $color . '>');
    }
}
