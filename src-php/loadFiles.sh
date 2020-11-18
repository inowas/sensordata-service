#!/bin/bash

docker-compose exec php bin/console app:load-uit-files
docker-compose exec php bin/console doctrine:query:sql "REFRESH MATERIALIZED VIEW public.view_data_6h;"
docker-compose exec php bin/console doctrine:query:sql "REFRESH MATERIALIZED VIEW public.view_data_12h;"
docker-compose exec php bin/console doctrine:query:sql "REFRESH MATERIALIZED VIEW public.view_data_1d;"
docker-compose exec php bin/console doctrine:query:sql "REFRESH MATERIALIZED VIEW public.view_data_2d;"
docker-compose exec php bin/console doctrine:query:sql "REFRESH MATERIALIZED VIEW public.view_data_1w;"
