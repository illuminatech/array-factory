<?php

namespace Illuminatech\ArrayFactory\Test\Support;

class Person
{
    public $name;

    public $email;

    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
    }
}
