from flask import abort, Flask, request, render_template, jsonify
from flask_cors import CORS, cross_origin
from flask_sqlalchemy import SQLAlchemy
import pandas as pd
import os
import time

db = SQLAlchemy()

app = Flask(__name__)
app.config['SQLALCHEMY_DATABASE_URI'] = os.environ.get("DATABASE_URI", default='')
app.secret_key = '2349978342978342907889709154089438989043049835890'
app.config['SESSION_TYPE'] = 'filesystem'
app.config['DEBUG'] = os.environ.get("DEBUG", default='false') == 'true'
app.config['SQLALCHEMY_DATABASE_URI'] = os.environ.get("DATABASE_URI", default='')
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = os.environ.get("DEBUG", default='false') == 'true'

CORS(app)
db.init_app(app)


@app.route('/', methods=['GET'])
@cross_origin()
def upload_file():
    if request.method == 'GET':
        return jsonify([])


@app.route('/list', methods=['GET'])
@app.route('/sensors', methods=['GET'])
@cross_origin()
def sensor_list():
    result = db.engine.execute("select * from view_sensor_parameters;")
    sensor_list = [dict(row) for row in result]
    return jsonify(sensor_list)


@app.route('/sensors/project/<project>/sensor/<sensor>/parameter/<parameter>')
@app.route('/sensors/project/<project>/sensor/<sensor>/property/<parameter>')
def sensor_data(project, sensor, parameter):
    valid_time_resolution_list = ['RAW', '6H', '12H', '1D', '2D', '1W']
    time_resolution = request.args.get('timeResolution', '1D').upper()

    start = int(request.args.get('start', '0'))
    end = int(request.args.get('end', str(int(time.time()))))

    # string not in the list
    if time_resolution not in valid_time_resolution_list:
        abort(400, 'Invalid timeResolution {0} provided. Valid values are: {1}'.format(
            time_resolution,
            ', '.join(valid_time_resolution_list)
        ))

    count = db.engine.execute("select count(*) from sensors s join parameters p on \
        s.id = p.sensor_id where s.name =\'{0}\' and s.project = \'{1}\' and p.name = \'{2}\';".format(
        sensor, project, parameter
    )).scalar()

    if count == 0:
        return jsonify([])

    query = ''
    if time_resolution == 'RAW':
        query = "select date_time, value from view_data_raw " \
                "where date_time > to_timestamp('{0}') " \
                "and date_time < to_timestamp('{1}') " \
                "and sensor_name='{2}' " \
                "and project = '{3}' " \
                "and parameter_name = '{4}'" \
            .format(start, end, sensor, project, parameter)

    if time_resolution == '6H':
        query = "select date_time, value from view_data_6h " \
                "where date_time > to_timestamp('{0}') " \
                "and date_time < to_timestamp('{1}') " \
                "and sensor_name='{2}' " \
                "and project_name = '{3}' " \
                "and parameter_name = '{4}'" \
            .format(start, end, sensor, project, parameter)

    if time_resolution == '12H':
        query = "select date_time, value from view_data_12h " \
                "where date_time > to_timestamp('{0}') " \
                "and date_time < to_timestamp('{1}') " \
                "and sensor_name='{2}' " \
                "and project_name = '{3}' " \
                "and parameter_name = '{4}'" \
            .format(start, end, sensor, project, parameter)

    if time_resolution == '1D':
        query = "select date_time, value from view_data_1d " \
                "where date_time > to_timestamp('{0}') " \
                "and date_time < to_timestamp('{1}') " \
                "and sensor_name='{2}' " \
                "and project_name = '{3}' " \
                "and parameter_name = '{4}'" \
            .format(start, end, sensor, project, parameter)

    if time_resolution == '2D':
        query = "select date_time, value from view_data_2d " \
                "where date_time > to_timestamp('{0}') " \
                "and date_time < to_timestamp('{1}') " \
                "and sensor_name='{2}' " \
                "and project_name = '{3}' " \
                "and parameter_name = '{4}'" \
            .format(start, end, sensor, project, parameter)

    if time_resolution == '1W':
        query = "select date_time, value from view_data_1w " \
                "where date_time > to_timestamp('{0}') " \
                "and date_time < to_timestamp('{1}') " \
                "and sensor_name='{2}' " \
                "and project_name = '{3}' " \
                "and parameter_name = '{4}'" \
            .format(start, end, sensor, project, parameter)

    df = pd.read_sql_query(query, db.engine)
    if (df.empty):
        return jsonify([])

    df.set_index('date_time') \
        .resample(time_resolution) \
        .interpolate(method='time') \
        .reset_index(level=0) \
        .rename(columns={"date_time": "ts", "value": "val"})

    return df.to_json(date_unit='s', orient='records')
