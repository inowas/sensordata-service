<?php

declare(strict_types=1);

namespace App\DataProcessing\WSVData;

/*
 {
    "uuid": "85d686f1-55b2-4d36-8dba-3207b50901a7",
    "number": "501040",
    "shortname": "PIRNA",
    "longname": "PIRNA",
    "km": 34.67,
    "agency": "WSA DRESDEN",
    "longitude": 13.929755188361455,
    "latitude": 50.96458457915114,
    "water": {
      "shortname": "ELBE",
      "longname": "ELBE"
    },
    "timeseries": [
      {
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

use DateTime;

class WsvApiStationResponse
{

    private $uuid;
    private $number;
    private $shortname;
    private $longname;
    private $longitude;
    private $latitude;

    private $timeseries = [];

    public static function fromJsonString(string $jsonStr): WsvApiStationResponse
    {
        $arr = json_decode($jsonStr, true, 512, JSON_THROW_ON_ERROR);
        return self::fromArr($arr);
    }

    public static function fromArr(array $arr): WsvApiStationResponse
    {
        $self = new self();
        $self->uuid = $arr['uuid'];
        $self->number = (int)$arr['number'];
        $self->shortname = $arr['shortname'];
        $self->longname = $arr['longname'];
        $self->longitude = (float)($arr['longitude'] ?? null);
        $self->latitude = (float)($arr['latitude'] ?? null);

        foreach ($arr['timeseries'] as $timeseries) {
            $self->timeseries[] = TimeSeries::fromArr($timeseries);
        }
        return $self;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function number(): int
    {
        return $this->number;
    }

    public function shortname(): string
    {
        return $this->shortname;
    }

    public function longname(): string
    {
        return $this->longname;
    }

    public function longitude(): float
    {
        return $this->longitude;
    }

    public function latitude(): float
    {
        return $this->latitude;
    }

    public function getGauge(string $tsShortName): ?float
    {
        /** @var TimeSeries $ts */
        $ts = $this->getTsByShortName($tsShortName);
        if (null === $ts) {
            return null;
        }

        return $ts->getGaugeZero()->getValue();
    }

    public function getWaterLevelInMeters(string $tsShortName): ?float
    {
        $ts = $this->getTsByShortName($tsShortName);
        if (null === $ts) {
            return null;
        }


        if ($ts->getUnit() === 'cm') {
            return $ts->getCurrentMeasurement()->getValue() / 100;
        }

        return $ts->getCurrentMeasurement()->getValue();
    }

    public function getDateTime(string $tsShortName): ?DateTime
    {
        $ts = $this->getTsByShortName($tsShortName);
        if (null === $ts) {
            return null;
        }

        return $ts->getCurrentMeasurement()->timestamp();
    }

    public function getTsByShortName(string $tsShortName): ?TimeSeries
    {
        $ts = null;
        /** @var TimeSeries $timeserie */
        foreach ($this->timeseries as $timeserie) {
            if ($timeserie->getShortname() === $tsShortName) {
                $ts = $timeserie;
            }
        }

        return $ts;
    }
}
