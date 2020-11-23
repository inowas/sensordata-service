<?php

declare(strict_types=1);

namespace App\DataProcessing\WSVData;


class GaugeZero
{

    private $unit;
    private $value;
    private $validFrom;

    public static function fromArr(array $arr): GaugeZero
    {
        $self = new self();
        $self->unit = $arr['unit'];
        $self->value = (float)$arr['value'];
        $self->validFrom = $arr['validFrom'];
        return $self;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getValidFrom(): string
    {
        return $this->validFrom;
    }

}
