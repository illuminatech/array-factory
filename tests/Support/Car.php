<?php

namespace Illuminatech\ArrayFactory\Test\Support;

class Car
{
    public $registrationNumber;

    private $type = 'unknown';

    private $color = 'unknown';

    private $engineRunning = false;

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function startEngine(): self
    {
        $this->engineRunning = true;

        return $this;
    }

    public function isEngineRunning(): bool
    {
        return $this->engineRunning;
    }

    public function setTypeImmutable(string $type)
    {
        $new = clone $this;
        $new->setType($type);

        return $new;
    }

    public function colorImmutable(string $color): self
    {
        $new = clone $this;
        $new->color($color);

        return $new;
    }
}
