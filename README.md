# Sensordata service

## Api 

List sensors and parameters

```
   $ http --json https://sds.example/list

   [{
        "id": "f44d391a-a6e7-4baf-ad60-c7c6a4ef6a57",
        "location": "",
        "name": "I-6",
        "parameters": [
            "ec",
            "ec_25",
            "h",
            "h_level",
            "ldo",
            "ph",
            "t",
            "t_intern",
            "v_batt"
        ],
        "project": "DEU1"
    }]
```

Get values from a specific sensor-parameter

```
   $ http --json https://sds.example/sensors/project/DEU1/sensor/I-6/parameter/ec"

   [{
        "date_time": 1559779200,
        "value": 0.3873
    },
    {
        "date_time": 1559865600,
        "value": 0.3893
    },
    {
        "date_time": 1559952000,
        "value": 0.388
    },
    {
        "date_time": 1560038400,
        "value": 0.3885
    },
    {
        "date_time": 1560124800,
        "value": 0.3877
    },
    {
        "date_time": 1560211200,
        "value": 0.3874
    },
    {
        "date_time": 1560297600,
        "value": 0.3869
    }]
```

Optional parameters as search string in url

* timeResolution ('RAW', '6H', '12H', '1D', '2D', '1W'), default: 1D
* dateFormat ('iso', 'epoch'), default: epoch
* start (unix timestamp)
* end (unix timestamp)
* gte (float, >= value)
* gt (float, > value)
* lte (float, <= value)
* lt (float, > value)
* excl (float, <> value)

Example:

```
http --json https://sds.example/sensors/project/DEU1/sensor/I-6/parameter/ec?timeResolution=1D&dateFormat=iso&start=1577836800&end=1609459200&gt=-100.0&excl=0
```

Get latest value from all sensors within a project

```
http --json https://sds.example/sensors/project/DEU1/latest
```

## Production use

```
$ docker-compose -f docker-compose.yml up -d
```


## Development

```
$ docker-compose -f docker-compose.yml  -f docker-compose.dev.yml up
```