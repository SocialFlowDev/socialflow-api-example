## SYNOPSIS

    git clone https://github.com/SocialFlowDev/socialflow-api-example/
    cd python
    pip install -r pip-requirements.txt
    python socialflow_oauth.py #Then follow the onscreen instructions.

### For the Web App Example

    cd python/webapp
    pip install -r pip-requirements.txt
    cp etc/config.yml.tpl etc/config.yml
    vi etc/config.yml #now edit etc/config.yml with your configuration values.
    python webapp.py

## DESCRIPTION

This repository is meant as a basic example of completing OAuth authentication and making API calls to SocialFlow's API.

There are two examples provided. The first, `python/socialflow_oauth.py` is for a non-web Authentication handshake.

The second, `python/webapp` is for a web based Authentication handshake.

Both  will authenticate into SocialFlow's OAuth API after you authenticate, and make an authenticated request. You can the store and reuse the retrieved tokens.
It will fetch a set of keys from SocialFlow after you authenticate, which you can store and reuse.


## REQUIREMENTS

You will need your application credentials, which you can obtain from your account manager at SocialFlow, and the SocialFlow API documentation, which can be obtained from your account manager as well.
You will also need access to the account that you want to use to make API calls with. We recommend creating a user specifically for the project that you are using the API for, then logging in as that user before running the script.

This example uses the 'rauth' module to work with the SocialFlow API.
It is documented here: https://rauth.readthedocs.org/en/latest/

The session referred to by 'OAuth1Session' is documented here:
http://docs.python-requests.org/en/latest/user/advanced/#session-objects

To add query parameters to the calls, see:
http://docs.python-requests.org/en/latest/user/quickstart/#passing-parameters-in-urls

The web based flow uses [Flask][1].

[1]: http://flask.pocoo.org/

## AUTHOR

Samuel Kaufman

CTO, SocialFlow, Inc.

