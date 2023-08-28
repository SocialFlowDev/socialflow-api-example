<?php
require __DIR__ . '/vendor/autoload.php';

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Exception\Exception;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\OAuth1\Signature\SignatureInterface;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\Session;
use OAuth\OAuth1\Service\AbstractService;
use OAuth\Common\Http\Client\CurlClient;
use OAuth\OAuth1\Signature\Signature;

class SocialflowService extends AbstractService
{
    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
    )
    {
        $signature = new Signature($credentials);
        $this->baseApiUri = new Uri('https://api.socialflow.com/');
        parent::__construct($credentials, $httpClient, $storage, $signature, $this->baseApiUri);

    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenEndpoint()
    {
        return new Uri('https://app.socialflow.com/oauth/request_token');
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {

        return new Uri('https://app.socialflow.com/oauth/authorize');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://app.socialflow.com/oauth/access_token');
    }

    /**
     * {@inheritdoc}
     */
    protected function parseRequestTokenResponse($responseBody)
    {
        parse_str($responseBody, $data);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (!isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] !== 'true') {
            throw new TokenResponseException('Error in retrieving token.');
        }

        return $this->parseAccessTokenResponse($responseBody);
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        parse_str($responseBody, $data);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response: ' . $responseBody);
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        } elseif (!isset($data['oauth_token']) || !isset($data['oauth_token_secret'])) {
            throw new TokenResponseException('Invalid response. OAuth Token data not set: ' . $responseBody);
        }

        $token = new StdOAuth1Token();

        $token->setRequestToken($data['oauth_token']);
        $token->setRequestTokenSecret($data['oauth_token_secret']);
        $token->setAccessToken($data['oauth_token']);
        $token->setAccessTokenSecret($data['oauth_token_secret']);

        $token->setEndOfLife(StdOAuth1Token::EOL_NEVER_EXPIRES);
        unset($data['oauth_token'], $data['oauth_token_secret']);
        $token->setExtraParams($data);

        return $token;
    }
}



// We need to use a persistent storage to save the token, because oauth1 requires the token secret received before'
// the redirect (request token request) in the access token request.
$storage = new Session();

if (getenv("CONSUMER_KEY") !== false) {
    $key = getenv("CONSUMER_KEY");
    $secret = getenv("CONSUMER_SECRET");
} else {
    echo "Consumer key:";
    $key = rtrim(fgets(STDIN));

    echo "Consumer secret:";
    $secret = rtrim(fgets(STDIN));
}

// Setup the credentials for the requests
$credentials = new Credentials(
    $key,
    $secret,
    'oob'
);

$http_client = new CurlClient();

// Disable this option if you do not want to get cluttered with curl output
$http_client->setCurlParameters([
    CURLOPT_VERBOSE => true,
]);

// Instantiate the service using the credentials, http client and storage mechanism for the token
$socialflowService = new SocialflowService($credentials, $http_client, $storage);

$token = $socialflowService->requestRequestToken();

$request_token = $token->getRequestToken();

$url = $socialflowService->getAuthorizationUri(['oauth_token' => $token->getRequestToken()]);

echo <<<EOT
        Go to $url
        authorize the account you wish to use for the SocialFlow API
        then enter your PIN below
EOT;

echo "PIN: ";
$pin = rtrim(fgets(STDIN));

$token = $socialflowService->requestAccessToken($token, $pin);

$access_token = $token->getAccessToken();
$access_token_secret = $token->getAccessTokenSecret();

function example_request($credentials, $access_token, $access_token_secret) {
    // Demonstrate a simple API request using consumer key/secret and
    // access token/secret pair

    $storage = new Session();

    $http_client = new CurlClient();

    // Disable this option if you do not want to get cluttered with curl output
    $http_client->setCurlParameters([
        CURLOPT_VERBOSE => true,
    ]);

    // Instantiate the service using the credentials, http client and storage mechanism for the token
    $socialflow = new SocialflowService($credentials, $http_client, $storage);


    // Now only store the access token in the empty storage
    $token = new StdOAuth1Token();

    $token->setAccessToken($access_token);
    $token->setAccessTokenSecret($access_token_secret);

    $storage->storeAccessToken($socialflow->service(), $token);

    // This request only uses the access token
    return $socialflow->request('/account/list?test=true');
}


echo <<<EOT
    ========

    Keep the following credentials somewhere secure.
    You can use 'example_request' in socialflow-example.php
    as a basis for how to make API calls against the SocialFlow API.

    consumer_key:         $key
    consumer_secret:      $secret
    access_token:         $access_token
    access_token_secret:  $access_token_secret

    ========

EOT;

echo example_request($credentials, $access_token, $access_token_secret);

?>
