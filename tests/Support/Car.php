<?php

namespace Illuminatech\ArrayFactory\Test\Support;

class Car
{
    public $registrationNumber;

    private $type = 'unknown';

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
