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

    /**
     * @var float|int
     */
    public $price = 0;

    public function __construct(Person $person, Car $car)
    {
        $this->person = $person;
        $this->car = $car;
    }

    public function setCar(Car $car)
    {
        $this->car = $car;

        return $this;
    }

    public function setPerson(Person $person)
    {
        $this->person = $person;

        return $person;
    }
}
