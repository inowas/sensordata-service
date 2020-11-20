from flask import abort, Flask, request, jsonify
from flask_cors import CORS, cross_origin
from flask_sqlalchemy import SQLAlchemy
import pandas as pd
import os

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

    if time_resolution not in valid_time_resolution_list:
        abort(400, 'Invalid timeResolution {0} provided. Valid values are: {1}'.format(
            time_resolution,
            ', '.join(valid_time_resolution_list)
        ))

    valid_date_formats = ['iso', 'epoch']
    date_format = request.args.get('dateFormat', 'iso').lower()

    if date_format not in valid_date_formats:
        abort(400, 'Invalid dateFormat {0} provided. Valid values are: {1}'.format(
            date_format,
            ', '.join(valid_date_formats)
        ))

    count = db.engine.execute("select count(*) from sensors s join parameters p on \
        s.id = p.sensor_id where s.name =\'{0}\' and s.project = \'{1}\' and p.name = \'{2}\';".format(
        sensor, project, parameter
    )).scalar()

    if count == 0:
        return jsonify([])

    query = "select date_time, value from view_data_raw " \
            "where sensor_name='{0}' " \
            "and project_name = '{1}' " \
            "and parameter_name = '{2}' " \
        .format(sensor, project, parameter)

    start = request.args.get('start', None) or request.args.get('begin', None)
    if start is not None:
        query += "and date_time >= to_timestamp({0}) ".format(int(start))

    end = request.args.get('end', None)
    if end is not None:
        query += "and date_time <= to_timestamp({0}) ".format(int(end))

    gte = request.args.get('gte', None) or request.args.get('min', None)
    if gte is not None:
        query += "and value >= {0} ".format(float(gte))

    gt = request.args.get('gte', None)
    if gt is not None:
        query += "and value > {0} ".format(float(gt))

    lte = request.args.get('lte', None) or request.args.get('max', None)
    if lte is not None:
        query += "and value <= {0} ".format(float(lte))

    lt = request.args.get('lt', None)
    if lt is not None:
        query += "and value < {0} ".format(float(lt))

    excl = request.args.get('excl', None)
    if excl is not None:
        query += "and value <> {0} ".format(float(excl))

    query += "order by date_time"
    df = pd.read_sql_query(query, db.engine)

    if (df.empty):
        return jsonify([])

    df = df.set_index('date_time')

    if time_resolution != 'RAW':
        df = df.resample(time_resolution).mean().interpolate(method='time')
        df = df.round(4)

    df = df.reset_index(level=0)
    return df.to_json(date_unit='s', date_format=date_format, orient='records')
