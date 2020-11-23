<?php

declare(strict_types=1);

namespace App\DataProcessing\WSVData;


use DateTime;

class CurrentMeasurement
{

    private $timestamp;
    private $value;

    public static function fromArr(array $arr): CurrentMeasurement
    {
        $self = new self();
        $self->timestamp = new DateTime($arr['timestamp']);
        $self->value = (float)$arr['value'];
        return $self;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function timestamp(): DateTime
    {
        return $this->timestamp;
    }
}
