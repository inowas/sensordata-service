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


## Production use

```
$ docker-compose -f docker-compose.yml up -d
```


## Development

```
$ docker-compose -f docker-compose.yml  -f docker-compose.dev.yml up
```