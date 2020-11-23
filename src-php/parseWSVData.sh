#!/bin/bash

set -e

docker-compose exec php bin/console app:parse-wsv PIRNA,DRESDEN
