#!/bin/bash

set -e

docker-compose exec -T php bin/console app:parse-ampeq:bra2 PT-01
docker-compose exec -T php bin/console app:parse-ampeq:bra2 PT-02
docker-compose exec -T php bin/console app:parse-ampeq:bra2 PZ-01
docker-compose exec -T php bin/console app:parse-ampeq:bra2 PZ-02
docker-compose exec -T php bin/console app:parse-ampeq:bra2 PZ-03
docker-compose exec -T php bin/console app:parse-ampeq:bra2 PZ-04
docker-compose exec -T php bin/console app:parse-ampeq:bra2 PZ-05
docker-compose exec -T php bin/console app:parse-ampeq:bra2 PZ-06
docker-compose exec -T php bin/console app:parse-ampeq:bra2 PZ-07
docker-compose exec -T php bin/console app:parse-ampeq:bra2 EPA-11
docker-compose exec -T php bin/console app:parse-ampeq:bra2 EPA-12
docker-compose exec -T php bin/console app:parse-ampeq:bra2 EPA-13
