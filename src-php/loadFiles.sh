#!/bin/bash

set -e

rsync --remove-source-files -azv ssh-w011ec33@w011ec33.kasserver.com:/www/htdocs/w011ec33/uit-sensor-data/processed/ ./data/UIT/inbox
docker-compose exec php bin/console app:load-uit-files
docker-compose exec php bin/console doctrine:query:sql "REFRESH MATERIALIZED VIEW public.view_data_6h;"
docker-compose exec php bin/console doctrine:query:sql "REFRESH MATERIALIZED VIEW public.view_data_12h;"
docker-compose exec php bin/console doctrine:query:sql "REFRESH MATERIALIZED VIEW public.view_data_1d;"
docker-compose exec php bin/console doctrine:query:sql "REFRESH MATERIALIZED VIEW public.view_data_2d;"
docker-compose exec php bin/console doctrine:query:sql "REFRESH MATERIALIZED VIEW public.view_data_1w;"
