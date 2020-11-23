<?php

declare(strict_types=1);

namespace App\DataProcessing\WSVData;

/*
 * {
        "shortname": "W",
        "longname": "WASSERSTAND ROHDATEN",
        "unit": "cm",
        "equidistance": 15,
        "currentMeasurement": {
          "timestamp": "2020-11-23T10:45:00+01:00",
          "value": 168.0,
          "trend": 0,
          "stateMnwMhw": "normal",
          "stateNswHsw": "unknown"
        },
        "gaugeZero": {
          "unit": "m. ü. NHN",
          "value": 108.006,
          "validFrom": "2004-02-01"
        }
      }
    ]
  }
 */

class TimeSeries
{

    private $shortname;
    private $longname;
    private $unit;
    private $equidistance;
    private $currentMeasurement;
    private $gaugeZero;

    public static function fromArr(array $arr): TimeSeries
    {
        $self = new self();
        $self->shortname = $arr['shortname'];
        $self->longname = $arr['longname'];
        $self->unit = $arr['unit'];
        $self->equidistance = (int)$arr['equidistance'];
        $self->currentMeasurement = CurrentMeasurement::fromArr($arr['currentMeasurement']);
        $self->gaugeZero = GaugeZero::fromArr($arr['gaugeZero']);
        return $self;
    }

    public function getShortname(): string
    {
        return $this->shortname;
    }

    public function getLongname(): string
    {
        return $this->longname;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function getEquidistance(): int
    {
        return $this->equidistance;
    }

    public function getCurrentMeasurement(): CurrentMeasurement
    {
        return $this->currentMeasurement;
    }

    public function getGaugeZero(): GaugeZero
    {
        return $this->gaugeZero;
    }
}
