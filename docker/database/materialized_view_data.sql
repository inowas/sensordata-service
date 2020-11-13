--
-- PostgreSQL database dump
--

-- Dumped from database version 13.1
-- Dumped by pg_dump version 13.0

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: materialized_view_data_12h; Type: MATERIALIZED VIEW; Schema: public; Owner: inowas
--

CREATE MATERIALIZED VIEW public.materialized_view_data_12h AS
 WITH data AS (
         SELECT s.id,
            s.project,
            s.name AS sensor_name,
            p.name AS parameter_name,
            to_timestamp((floor(((d."timestamp")::double precision / (43200)::double precision)) * (43200)::double precision)) AS date_time,
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
    data.date_time AS date,
    avg(data.value) AS avg_6h
   FROM data
  GROUP BY data.date_time, data.id, data.project, data.sensor_name, data.parameter_name
  WITH NO DATA;


ALTER TABLE public.materialized_view_data_12h OWNER TO inowas;

--
-- Name: materialized_view_data_1d; Type: MATERIALIZED VIEW; Schema: public; Owner: inowas
--

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

--
-- Name: materialized_view_data_1w; Type: MATERIALIZED VIEW; Schema: public; Owner: inowas
--

CREATE MATERIALIZED VIEW public.materialized_view_data_1w AS
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
    (date_trunc('week'::text, data.date_time))::date AS date,
    avg(data.value) AS avg_daily_value
   FROM data
  GROUP BY (date_trunc('week'::text, data.date_time)), data.id, data.project, data.sensor_name, data.parameter_name
  WITH NO DATA;


ALTER TABLE public.materialized_view_data_1w OWNER TO inowas;

--
-- Name: materialized_view_data_6h; Type: MATERIALIZED VIEW; Schema: public; Owner: inowas
--

CREATE MATERIALIZED VIEW public.materialized_view_data_6h AS
 WITH data AS (
         SELECT s.id,
            s.project,
            s.name AS sensor_name,
            p.name AS parameter_name,
            to_timestamp((floor(((d."timestamp")::double precision / (21600)::double precision)) * (21600)::double precision)) AS date_time,
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
    data.date_time AS date,
    avg(data.value) AS avg_6h
   FROM data
  GROUP BY data.date_time, data.id, data.project, data.sensor_name, data.parameter_name
  WITH NO DATA;


ALTER TABLE public.materialized_view_data_6h OWNER TO inowas;

--
-- Name: materialized_view_data_12h; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: inowas
--

REFRESH MATERIALIZED VIEW public.materialized_view_data_12h;


--
-- Name: materialized_view_data_1d; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: inowas
--

REFRESH MATERIALIZED VIEW public.materialized_view_data_1d;


--
-- Name: materialized_view_data_1w; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: inowas
--

REFRESH MATERIALIZED VIEW public.materialized_view_data_1w;


--
-- Name: materialized_view_data_6h; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: inowas
--

REFRESH MATERIALIZED VIEW public.materialized_view_data_6h;


--
-- PostgreSQL database dump complete
--

