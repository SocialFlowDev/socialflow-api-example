from flask import Flask, request, url_for, redirect, session, render_template
from flaskrun import flaskrun
import yaml
import os,inspect
from rauth import OAuth1Service, OAuth1Session


def _config():
     cwd = os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe())))
     configFile = os.path.join(cwd,"etc","config.yml")
     cfg = None
     with open(configFile,"r") as f:
         _cfg = yaml.load(f)
         cfg = _cfg
     if cfg is None:
        raise Exception("etc/config.yml failed to load.")
     if cfg.get('config',None) is None:
        raise Exception("etc/config.yml appears to be malformed, please check it against etc/config.yml.tpl")
     return cfg['config']


class WebApp():

    base_url = None
    api_base_url = None
    consumer_secret = None
    consumer_key = None
    oauth_service = None
    session_secret_key = None

    def __init__(self,config):
        sf_config = config.get('socialflow_config',None)
        if sf_config is None:
            raise Exception("socialflow_config key of etc/config.yml is missing, please refer to etc/config.yml.tpl")

        self.base_url = sf_config['base_url']
        self.api_base_url = sf_config['api_base_url']
        creds = config.get('creds',None)
        if creds is None:
            raise Exception("creds key of etc/config.yml is missing, please refer to etc/config.yml.tpl")

        self.consumer_secret = creds['consumer_secret']
        self.consumer_key = creds['consumer_key']
        self.session_secret_key = creds['session_secret_key']
        self.oauth_service = self._oauth_service()

    def _url_for(self,rel_path):
        return self.base_url + rel_path


    def _api_url_for(self,rel_path):
        return self.api_base_url + rel_path

    def _oauth_service(self):
        return OAuth1Service(
            consumer_key=self.consumer_key,
            consumer_secret=self.consumer_secret,
            name='socialflow',
            access_token_url=self._url_for('/oauth/access_token'),
            authorize_url=self._url_for('/oauth/authorize'),
            request_token_url=self._url_for('/oauth/request_token'),
            base_url=self.base_url)


    def oauth_callback(self):
        oauth_token = request.args.get('oauth_token')
        assert(oauth_token)
        oauth_verifier = request.args.get('oauth_verifier')
        assert(oauth_verifier)
        token= self.oauth_service.get_access_token(
                session['request_token'],
                session['request_token_secret'],
                params={"oauth_verifier":oauth_verifier}
                )
        session['access_token'],session['access_token_secret'] = token
        return render_template('access_granted.html',
                access_token=session['access_token'],
                access_token_secret=session['access_token_secret'],
                )

    def index(self):
        return render_template("index.html")

    def authorize(self):
        request_token, request_token_secret = self.oauth_service.get_request_token(
                params={"oauth_callback": url_for("oauth_callback", _external=True)}
                )
        session['request_token'] = request_token
        session['request_token_secret'] = request_token_secret
        return redirect(self.oauth_service.get_authorize_url(request_token), code=302)


app = Flask(__name__)

if __name__ == "__main__":
    webApp = WebApp(_config())
    app.secret_key = webApp.session_secret_key
    app.config['SESSION_TYPE'] = 'filesystem'

    @app.route("/")
    def index():
        return webApp.index()

    @app.route("/authorize")
    def authorize():
        return webApp.authorize()

    @app.route("/oauth_callback")
    def oauth_callback():
        return webApp.oauth_callback()

    flaskrun( app )
