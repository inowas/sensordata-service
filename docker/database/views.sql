-- BEGIN RAW DATA VIEW --
create view view_data_raw(id, project, sensor_name, parameter_name, date_time, value) as
SELECT s.id                                          AS sensor_id,
       s.project                                     AS project_name,
       s.name                                        AS sensor_name,
       p.name                                        AS parameter_name,
       to_timestamp(d."timestamp"::double precision) AS date_time,
       d.value                                       AS value
FROM sensors s
         LEFT JOIN parameters p ON s.id = p.sensor_id
         LEFT JOIN datasets ds ON p.id = ds.parameter_id
         LEFT JOIN data d ON ds.id = d.dataset_id;

alter table view_data_raw
    owner to inowas;
-- END RAW DATA VIEW --

-- BEGIN 6H DATA VIEW --
create materialized view view_data_6h as
WITH data AS (
    SELECT s.id                                  AS sensor_id,
           s.project                             AS project_name,
           s.name                                AS sensor_name,
           p.name                                AS parameter_name,
           to_timestamp(floor(d."timestamp"::double precision / 21600::double precision) *
                        21600::double precision) AS date_time,
           d.value                               AS value
    FROM sensors s
             LEFT JOIN parameters p ON s.id = p.sensor_id
             LEFT JOIN datasets ds ON p.id = ds.parameter_id
             LEFT JOIN public.data d ON ds.id = d.dataset_id
    GROUP BY (to_timestamp(d."timestamp"::double precision)), s.id, s.project, s.name, s.location, p.id, p.type, p.name,
             d."timestamp", (to_timestamp(d."timestamp"::double precision)::date), d.value
)
SELECT data.sensor_id                     AS sensor_id,
       data.project_name                  AS project_name,
       data.sensor_name                   AS sensor_name,
       data.parameter_name                AS parameter_name,
       data.date_time                     AS date_time,
       round(avg(data.value)::numeric, 4) AS value
FROM data
GROUP BY data.sensor_id, data.project_name, data.sensor_name, data.parameter_name, data.date_time;

alter materialized view view_data_6h owner to inowas;
-- END 6H DATA VIEW --

-- BEGIN 12H DATA VIEW --
create materialized view view_data_12h as
WITH data AS (
    SELECT s.id                                  AS sensor_id,
           s.project                             AS project_name,
           s.name                                AS sensor_name,
           p.name                                AS parameter_name,
           to_timestamp(floor(d."timestamp"::double precision / 43200::double precision) *
                        43200::double precision) AS date_time,
           d.value                               AS value
    FROM sensors s
             LEFT JOIN parameters p ON s.id = p.sensor_id
             LEFT JOIN datasets ds ON p.id = ds.parameter_id
             LEFT JOIN public.data d ON ds.id = d.dataset_id
    GROUP BY (to_timestamp(d."timestamp"::double precision)), s.id, s.project, s.name, s.location, p.id, p.type, p.name,
             d."timestamp", (to_timestamp(d."timestamp"::double precision)::date), d.value
)
SELECT data.sensor_id                     AS sensor_id,
       data.project_name                  AS project_name,
       data.sensor_name                   AS sensor_name,
       data.parameter_name                AS parameter_name,
       data.date_time                     AS date_time,
       round(avg(data.value)::numeric, 4) AS value
FROM data
GROUP BY data.sensor_id, data.project_name, data.sensor_name, data.parameter_name, data.date_time;

alter materialized view view_data_12h owner to inowas;
-- END 12H DATA VIEW --

-- BEGIN 1D DATA VIEW --
create materialized view view_data_1d as
WITH data AS (
    SELECT s.id                                          AS sensor_id,
           s.project                                     AS project_name,
           s.name                                        AS sensor_name,
           p.name                                        AS parameter_name,
           to_timestamp(d."timestamp"::double precision) AS date_time,
           d.value                                       AS value
    FROM sensors s
             LEFT JOIN parameters p ON s.id = p.sensor_id
             LEFT JOIN datasets ds ON p.id = ds.parameter_id
             LEFT JOIN public.data d ON ds.id = d.dataset_id
    GROUP BY d.value, s.project, s.name, p.name, to_timestamp(d."timestamp"::double precision), s.id
)
SELECT data.sensor_id                          AS sensor_id,
       data.project_name                       AS project_name,
       data.sensor_name                        AS sensor_name,
       data.parameter_name                     AS parameter_name,
       date_trunc('day'::text, data.date_time) AS date_time,
       round(avg(data.value)::numeric, 4)      AS value
FROM data
GROUP BY data.sensor_id, data.project_name, data.sensor_name, data.parameter_name, date_time;

alter materialized view view_data_1d owner to inowas;
-- END 1D DATA VIEW --

-- BEGIN 1W DATA VIEW --
create materialized view view_data_1w as
WITH data AS (
    SELECT s.id                                          AS sensor_id,
           s.project                                     AS project_name,
           s.name                                        AS sensor_name,
           p.name                                        AS parameter_name,
           to_timestamp(d."timestamp"::double precision) AS date_time,
           d.value                                       AS value
    FROM sensors s
             LEFT JOIN parameters p ON s.id = p.sensor_id
             LEFT JOIN datasets ds ON p.id = ds.parameter_id
             LEFT JOIN public.data d ON ds.id = d.dataset_id
    GROUP BY d.value, s.project, s.name, p.name, to_timestamp(d."timestamp"::double precision), s.id
)
SELECT data.sensor_id                           AS sensor_id,
       data.project_name                        AS project_name,
       data.sensor_name                         AS sensor_name,
       data.parameter_name                      AS parameter_name,
       date_trunc('week'::text, data.date_time) AS date_time,
       round(avg(data.value)::numeric, 4)       AS value
FROM data
GROUP BY data.sensor_id, data.project_name, data.sensor_name, data.parameter_name, date_time;

alter materialized view view_data_1w owner to inowas;
-- END 1W DATA VIEW --