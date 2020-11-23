<?php

namespace App\Tests\DataProcessing\WSVData;

use App\DataProcessing\WSVData\WsvApiStationResponse;
use DateTime;
use PHPUnit\Framework\TestCase;

class WsvApiStationResponseTest extends TestCase
{

    public function testInstatiation()
    {

        $str = '{
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
        }';

        $response = WsvApiStationResponse::fromJsonString($str);
        self::assertInstanceOf(WsvApiStationResponse::class, $response);

        $arr = json_decode($str, true, 512, JSON_THROW_ON_ERROR);
        $response = WsvApiStationResponse::fromArr($arr);
        self::assertInstanceOf(WsvApiStationResponse::class, $response);

        self::assertEquals($arr['uuid'], $response->uuid());
        self::assertEquals((int)$arr['number'], $response->number());
        self::assertEquals($arr['shortname'], $response->shortname());
        self::assertEquals($arr['longname'], $response->longname());
        self::assertEquals((float)$arr['longitude'], $response->longitude());
        self::assertEquals((float)$arr['latitude'], $response->latitude());
        self::assertEquals(108.006, $response->getGauge('W'));
        self::assertEquals(1.680, $response->getWaterLevelInMeters('W'));
        self::assertEquals(new DateTime('2020-11-23T10:45:00+01:00'), $response->getDateTime('W'));
    }
}
