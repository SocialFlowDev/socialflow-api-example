from flask import Flask
from flaskrun import flaskrun
app = Flask(__name__)

@app.route("/")
def hello():
    return "Hello World!"

if __name__ == "__main__":
    flaskrun( app )
