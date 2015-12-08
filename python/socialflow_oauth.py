"""
    Example script to authenticate into SocialFlow's OAuth API.
    USAGE: python socialflow_oauth.py
    follow on screen instructions.

    you can then save your access_token and access_token_secret and reuse
    it in later scripts.
"""

from rauth import OAuth1Service, OAuth1Session

BASE_URL = "http://www.socialflow.com"
API_BASE_URL = "http://api.socialflow.com"


def url_for(rel_path):
    return BASE_URL + rel_path


def api_url_for(rel_path):
    return API_BASE_URL + rel_path


def fetch_access_token(consumer_key, consumer_secret):
    socialflow = OAuth1Service(
        consumer_key=consumer_key,
        consumer_secret=consumer_secret,
        name='socialflow',
        access_token_url=url_for('/oauth/access_token'),
        authorize_url=url_for('/oauth/authorize'),
        request_token_url=url_for('/oauth/request_token'),
        base_url=BASE_URL)
    request_token, request_token_secret = socialflow.get_request_token(
        method='GET',
        params={'oauth_callback': 'oob'})
    authorize_url = socialflow.get_authorize_url(request_token)
    print """Go to %s
        authorize the account you wish to use for the SocialFlow API
        then enter your PIN below""" % authorize_url
    pin = raw_input("PIN: ")
    pin = pin.strip()
    return socialflow.get_access_token(
        request_token,
        request_token_secret,
        params={'oauth_verifier': pin})


def example_request(
        consumer_key,
        consumer_secret,
        token,
        token_secret):
    api = OAuth1Session(
        consumer_key,
        consumer_secret,
        access_token=token,
        access_token_secret=token_secret)
    return api.get(api_url_for('/account/list'))


def run():
    key = raw_input("Consumer key:")
    secret = raw_input("Consumer secret:")
    key = key.strip()
    secret = secret.strip()
    access_token, access_token_secret = fetch_access_token(key, secret)
    print """
    access_token: %(access_token)s
    access_token_secret: %(access_token_secret)s
    """ % {"access_token": access_token,
           "access_token_secret": access_token_secret}
    print example_request(key, secret, access_token, access_token_secret).text

if __name__ == '__main__':
    run()
