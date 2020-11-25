-- BEGIN SENSOR PARAMETER VIEW --
drop view if exists view_sensor_parameters;
create view view_sensor_parameters(id, name, project, location, parameters) as
WITH sensors AS (
    SELECT s_1.id,
           s_1.project,
           s_1.name,
           s_1.location,
           p.name AS parameter_name
    FROM public.sensors s_1
             JOIN parameters p ON s_1.id = p.sensor_id
    GROUP BY p.type, s_1.id, s_1.project, s_1.name, p.name
    ORDER BY s_1.id, p.name
)

SELECT s.id,
       s.name,
       s.project,
       s.location,
       array_agg(s.parameter_name) AS parameters
FROM sensors s
GROUP BY s.id, s.name, s.project, s.location
ORDER BY s.project, s.name;

alter table view_sensor_parameters
    owner to inowas;

alter table view_sensor_parameters
    owner to inowas;
-- END SENSOR PARAMETER VIEW --


-- BEGIN RAW DATA VIEW --
drop view if exists view_data_raw;
create view view_data_raw as
SELECT s.id                                          AS sensor_id,
       s.project                                     AS project_name,
       s.name                                        AS sensor_name,
       p.id                                          AS parameter_id,
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

-- BEGIN FILTERED T-DATA RAW VIEW --
drop view if exists view_data_t;
create view view_data_t as
SELECT s.id                                          AS sensor_id,
       s.project                                     AS project_name,
       s.name                                        AS sensor_name,
       p.id                                          AS parameter_id,
       p.name                                        AS parameter_name,
       to_timestamp(d."timestamp"::double precision) AS date_time,
       d.value                                       AS value
FROM sensors s
         LEFT JOIN parameters p ON s.id = p.sensor_id
         LEFT JOIN datasets ds ON p.id = ds.parameter_id
         LEFT JOIN data d ON ds.id = d.dataset_id
WHERE d.value::double precision > -100::double precision
  and d.value::double precision <> 0::double precision
  and (p.type = 't' or p.type = 't_intern');

alter table view_data_t
    owner to inowas;
-- END FILTERED T-DATA RAW VIEW --

-- BEGIN 6H DATA VIEW --
drop materialized view if exists view_data_6h;
create materialized view view_data_6h as
WITH data AS (
    SELECT s.id      AS sensor_id,
           s.project AS project_name,
           s.name    AS sensor_name,
           p.id      AS parameter_id,
           p.name    AS parameter_name,
           to_timestamp(floor(d."timestamp"::double precision / 21600::double precision) *
                        21600::double precision + 10800::double precision
               )     AS date_time,
           d.value   AS value
    FROM sensors s
             LEFT JOIN parameters p ON s.id = p.sensor_id
             LEFT JOIN datasets ds ON p.id = ds.parameter_id
             LEFT JOIN public.data d ON ds.id = d.dataset_id
    GROUP BY s.id, s.project, s.name, p.id, p.name, d.value, d.timestamp
)
SELECT data.sensor_id                     AS sensor_id,
       data.project_name                  AS project_name,
       data.sensor_name                   AS sensor_name,
       data.parameter_id                  AS parameter_id,
       data.parameter_name                AS parameter_name,
       data.date_time                     AS date_time,
       round(avg(data.value)::numeric, 4) AS avg,
       min(data.value)                    as min,
       max(data.value)                    as max

FROM data
GROUP BY data.sensor_id, data.project_name, data.sensor_name, data.parameter_id, data.parameter_name, data.date_time;

alter materialized view view_data_6h owner to inowas;
-- END 6H DATA VIEW --

-- BEGIN 12H DATA VIEW --
drop materialized view if exists view_data_12h;
create materialized view view_data_12h as
WITH data AS (
    SELECT s.id      AS sensor_id,
           s.project AS project_name,
           s.name    AS sensor_name,
           p.id      AS parameter_id,
           p.name    AS parameter_name,
           to_timestamp(floor(d."timestamp"::double precision / 43200::double precision) *
                        43200::double precision + 21600::double precision
               )     AS date_time,
           d.value   AS value
    FROM sensors s
             LEFT JOIN parameters p ON s.id = p.sensor_id
             LEFT JOIN datasets ds ON p.id = ds.parameter_id
             LEFT JOIN public.data d ON ds.id = d.dataset_id
    GROUP BY s.id, s.project, s.name, p.id, p.name, d.value, d.timestamp
)
SELECT data.sensor_id                     AS sensor_id,
       data.project_name                  AS project_name,
       data.sensor_name                   AS sensor_name,
       data.parameter_id                  AS parameter_id,
       data.parameter_name                AS parameter_name,
       data.date_time                     AS date_time,
       round(avg(data.value)::numeric, 4) AS avg,
       min(data.value)                    as min,
       max(data.value)                    as max
FROM data
GROUP BY data.sensor_id, data.project_name, data.sensor_name, data.parameter_id, data.parameter_name, data.date_time;

alter materialized view view_data_12h owner to inowas;
-- END 12H DATA VIEW --

-- BEGIN 1D DATA VIEW --
drop materialized view if exists view_data_1d;
create materialized view view_data_1d as
WITH data AS (
    SELECT s.id      AS sensor_id,
           s.project AS project_name,
           s.name    AS sensor_name,
           p.id      AS parameter_id,
           p.name    AS parameter_name,
           to_timestamp(floor(d."timestamp"::double precision / 86400::double precision) *
                        86400::double precision + 43200::double precision
               )     AS date_time,
           d.value   AS value
    FROM sensors s
             LEFT JOIN parameters p ON s.id = p.sensor_id
             LEFT JOIN datasets ds ON p.id = ds.parameter_id
             LEFT JOIN public.data d ON ds.id = d.dataset_id
    GROUP BY s.id, s.project, s.name, p.id, p.name, d.value, d.timestamp
)
SELECT data.sensor_id                     AS sensor_id,
       data.project_name                  AS project_name,
       data.sensor_name                   AS sensor_name,
       data.parameter_id                  AS parameter_id,
       data.parameter_name                AS parameter_name,
       data.date_time                     AS date_time,
       round(avg(data.value)::numeric, 4) AS avg,
       min(data.value)                    as min,
       max(data.value)                    as max
FROM data
GROUP BY data.sensor_id, data.project_name, data.sensor_name, data.parameter_id, data.parameter_name, data.date_time;

alter materialized view view_data_1d owner to inowas;
-- END 1D DATA VIEW --

-- BEGIN 2D DATA VIEW --
drop materialized view if exists view_data_2d;
create materialized view view_data_2d as
WITH data AS (
    SELECT s.id      AS sensor_id,
           s.project AS project_name,
           s.name    AS sensor_name,
           p.id      AS parameter_id,
           p.name    AS parameter_name,
           to_timestamp(floor(d."timestamp"::double precision / 172800::double precision) *
                        172800::double precision + 86400::double precision
               )     AS date_time,
           d.value   AS value
    FROM sensors s
             LEFT JOIN parameters p ON s.id = p.sensor_id
             LEFT JOIN datasets ds ON p.id = ds.parameter_id
             LEFT JOIN public.data d ON ds.id = d.dataset_id
    GROUP BY s.id, s.project, s.name, p.id, p.name, d.value, d.timestamp
)
SELECT data.sensor_id                     AS sensor_id,
       data.project_name                  AS project_name,
       data.sensor_name                   AS sensor_name,
       data.parameter_id                  AS parameter_id,
       data.parameter_name                AS parameter_name,
       data.date_time                     AS date_time,
       round(avg(data.value)::numeric, 4) AS avg,
       min(data.value)                    as min,
       max(data.value)                    as max
FROM data
GROUP BY data.sensor_id, data.project_name, data.sensor_name, data.parameter_id, data.parameter_name, data.date_time;

alter materialized view view_data_2d owner to inowas;
-- END 2D DATA VIEW --

-- BEGIN 1W DATA VIEW --
drop materialized view if exists view_data_1w;
create materialized view view_data_1w as
WITH data AS (
    SELECT s.id      AS sensor_id,
           s.project AS project_name,
           s.name    AS sensor_name,
           p.id      AS parameter_id,
           p.name    AS parameter_name,
           to_timestamp(floor(d."timestamp"::double precision / 604800::double precision) *
                        604800::double precision + 302400::double precision
               )     AS date_time,
           d.value   AS value
    FROM sensors s
             LEFT JOIN parameters p ON s.id = p.sensor_id
             LEFT JOIN datasets ds ON p.id = ds.parameter_id
             LEFT JOIN public.data d ON ds.id = d.dataset_id
    GROUP BY s.id, s.project, s.name, p.id, p.name, d.value, d.timestamp
)
SELECT data.sensor_id                     AS sensor_id,
       data.project_name                  AS project_name,
       data.sensor_name                   AS sensor_name,
       data.parameter_id                  AS parameter_id,
       data.parameter_name                AS parameter_name,
       data.date_time                     AS date_time,
       round(avg(data.value)::numeric, 4) AS avg,
       min(data.value)                    as min,
       max(data.value)                    as max
FROM data
GROUP BY data.sensor_id, data.project_name, data.sensor_name, data.parameter_id, data.parameter_name, data.date_time;

alter materialized view view_data_1w owner to inowas;
-- END 1W DATA VIEW --