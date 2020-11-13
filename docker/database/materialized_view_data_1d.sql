CREATE MATERIALIZED VIEW public.materialized_view_data_1d AS
 WITH data AS (
         SELECT s.id,
            s.project,
            s.name AS sensor_name,
            p.name AS parameter_name,
            d."timestamp",
            to_timestamp((d."timestamp")::double precision) AS date_time,
            d.value
           FROM (((public.sensors s
             LEFT JOIN public.parameters p ON ((s.id = p.sensor_id)))
             LEFT JOIN public.datasets ds ON ((p.id = ds.parameter_id)))
             LEFT JOIN public.data d ON ((ds.id = d.dataset_id)))
          GROUP BY (to_timestamp((d."timestamp")::double precision)), s.id, s.project, s.name, s.location, p.id, p.type, p.name, d."timestamp", ((to_timestamp((d."timestamp")::double precision))::date), d.value
        )
 SELECT data.id,
    data.project,
    data.sensor_name,
    data.parameter_name,
    (date_trunc('day'::text, data.date_time))::date AS date,
    avg(data.value) AS avg_daily_value
   FROM data
  GROUP BY (date_trunc('day'::text, data.date_time)), data.id, data.project, data.sensor_name, data.parameter_name
  WITH NO DATA;


ALTER TABLE public.materialized_view_data_1d OWNER TO inowas;
REFRESH MATERIALIZED VIEW public.materialized_view_data_1d;
