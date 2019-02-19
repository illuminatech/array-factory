<?php

namespace Illuminatech\ArrayFactory\Test\Support;

class Person
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $email;

    /**
     * @var Car[]
     */
    public $carRents = [];

    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    public function rentCar(Car $car, $price): CarRent
    {
        $rent = new CarRent($this, $car);
        $rent->price = $price;

        $this->carRents[] = $rent;

        return $rent;
    }
}
