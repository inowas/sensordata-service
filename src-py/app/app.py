from flask import abort, Flask, request, render_template, jsonify
from flask_cors import CORS, cross_origin
from flask_sqlalchemy import SQLAlchemy
import os

db = SQLAlchemy()

app = Flask(__name__)
CORS(app)
db.init_app(app)


@app.route('/', methods=['GET'])
@cross_origin()
def upload_file():
    result = db.engine.execute(
        "select d from public.view_data_1w where sensor_name='{0}' and project_name = '{1}' and parameter_name = '{2}'".format(
            'I-3', 'DEU1', 'ec'
        )
    )
    rows = [dict(row) for row in result]
    print(rows)
    if request.method == 'GET':
        return render_template('upload.html')


@app.route('/list', methods=['GET'])
@cross_origin()
def sensor_list():
    result = db.engine.execute("select * from sensors;")
    sensor_list = [dict(row) for row in result]

    for sensor in sensor_list:
        result = db.engine.execute(
            "select p.type from sensors join parameters p on sensors.id = p.sensor_id where sensors.id::text =\'{0}\' group by p.type;".format(
                str(sensor['id'])))
        sensor['parameters'] = [dict(row)['type'] for row in result]

    return jsonify(sensor_list)


@app.route('/sensors/project/<project>/sensor/<sensor>/parameter/<parameter>')
def sensor_data(project, sensor, parameter):
    valid_time_resolution_list = ['RAW', '6H', '12H', '1D', '1W']
    time_resolution = request.args.get('timeResolution', '1D')

    # string not in the list
    if time_resolution not in valid_time_resolution_list:
        abort(400, 'Invalid timeResolution {0} provided. Valid values are: '.format(
            time_resolution,
            ', '.join(valid_time_resolution_list)
        ))

    count = db.engine.execute("select count(*) from sensors s join parameters p on \
        s.id = p.sensor_id where s.name =\'{0}\' and s.project = \'{1}\' and p.name = \'{2}\';".format(
        sensor, project, parameter
    )).scalar()

    if count == 0:
        return jsonify([])

    if time_resolution == 'RAW':
        result = db.engine.execute(
            "select * from public.view_data_raw where sensor_name='{0}' and project = '{1}' and parameter_name = '{2}'".format(
                sensor, project, parameter
            )
        )
        return jsonify([dict(row) for row in result])

    if time_resolution == '6H':
        result = db.engine.execute(
            "select * from public.materialized_view_data_6h where sensor_name='{0}' and project = '{1}' and parameter_name = '{2}'".format(
                sensor, project, parameter
            )
        )
        return jsonify([dict(row) for row in result])

    if time_resolution == '12H':
        result = db.engine.execute(
            "select * from public.materialized_view_data_12h where sensor_name='{0}' and project = '{1}' and parameter_name = '{2}'".format(
                sensor, project, parameter
            )
        )
        return jsonify([dict(row) for row in result])

    if time_resolution == '1D':
        result = db.engine.execute(
            "select * from public.materialized_view_data_1d where sensor_name='{0}' and project = '{1}' and parameter_name = '{2}'".format(
                sensor, project, parameter
            )
        )
        return jsonify([dict(row) for row in result])

    if time_resolution == '1W':
        result = db.engine.execute(
            "select * from public.materialized_view_data_1w where sensor_name='{0}' and project = '{1}' and parameter_name = '{2}'".format(
                sensor, project, parameter
            )
        )
        return jsonify([dict(row) for row in result])

    return jsonify([])


if __name__ == '__main__':
    app.secret_key = '2349978342978342907889709154089438989043049835890'
    app.config['SESSION_TYPE'] = 'filesystem'
    app.config['DEBUG'] = os.environ.get("DEBUG", default='false') == 'true'
    app.config['SQLALCHEMY_DATABASE_URI'] = os.environ.get("DATABASE_URI", default='')
    app.run(debug=app.config['DEBUG'], host='0.0.0.0')
