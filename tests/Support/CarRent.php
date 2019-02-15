<?php

namespace Illuminatech\ArrayFactory\Test\Support;

class CarRent
{
    /**
     * @var Person
     */
    public $person;

    /**
     * @var Car
     */
    public $car;

    public function __construct(Person $person, Car $car)
    {
        $this->person = $person;
        $this->car = $car;
    }
}
