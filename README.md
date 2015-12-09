## SYNOPSIS

    git clone https://github.com/SocialFlowDev/socialflow-api-example/
    cd python
    pip install -r pip-requirements.txt
    python socialflow_oauth.py #Then follow the onscreen instructions.

##DESCRIPTION

This is an example python script that will authenticate into SocialFlow's OAuth API and make an authenticated request.
It will fetch a set of keys from SocialFlow after you authenticate, which you can store and reuse.

##REQUIREMENTS

You will need your application credentials, which you can obtain from your account manager at SocialFlow, and the SocialFlow API documentation, which can be obtained from your account manager as well.
You will also need access to the account that you want to use to make API calls with. We recommend creating a user specifically for the project that you are using the API for, then logging in as that user before running the script.

##AUTHOR

Samuel Kaufman

CTO, SocialFlow, Inc.

