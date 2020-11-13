with data as (select s.id                      as id,
                     s.project                 as project,
                     s.name                    as sensor_name,
                     p.name                    as parameter_name,
                     d.timestamp,
                     to_timestamp(d.timestamp) as date_time,
                     d.value
              from sensors as s
                       left join parameters p on s.id = p.sensor_id
                       left join datasets ds on p.id = ds.parameter_id
                       left join data d on ds.id = d.dataset_id
              group by date_time, s.id, s.project, s.name, s.location, p.id, p.type, p.name, d.timestamp,
                       to_timestamp(d.timestamp)::date, d.value)

SELECT data.id                                 as id,
       data.project                            as project,
       data.sensor_name                        as sensor_name,
       data.parameter_name                     as parameter_name,
       DATE_TRUNC('day', data.date_time)::date AS date,
       avg(data.value)                         as avg_daily_value
FROM data
GROUP BY DATE_TRUNC('day', data.date_time), data.id, data.project, data.sensor_name, data.parameter_name;
