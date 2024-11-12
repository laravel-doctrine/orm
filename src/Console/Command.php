<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Console;

use Illuminate\Console\Command as IlluminateCommand;

class Command extends IlluminateCommand
{
    public function message(string $message, string $color = 'blue'): void
    {
        $this->getOutput()->writeln('<fg=' . $color . '>' . $message . '</fg=' . $color . '>');
    }
}
