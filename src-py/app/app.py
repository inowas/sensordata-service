from flask import Flask, request, render_template
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
    result = db.engine.execute("select count(*) from sensors as s left join sensor_parameters sp on s.id = sp.sensor_id left join datasets d on sp.id = d.parameter_id left join data d2 on d.id = d2.dataset_id;")
    print(str(result.fetchall()))
    if request.method == 'GET':
        return render_template('upload.html')


if __name__ == '__main__':
    app.secret_key = '2349978342978342907889709154089438989043049835890'
    app.config['SESSION_TYPE'] = 'filesystem'
    app.config['DEBUG'] = True
    app.config['SQLALCHEMY_DATABASE_URI'] = 'postgresql://inowas:inowas@localhost:5432/inowas'
    app.config['SQLALCHEMY_DATABASE_URI'] = os.environ.get("DATABASE_URI", default='')
    app.run(debug=app.config['DEBUG'], host='0.0.0.0')
