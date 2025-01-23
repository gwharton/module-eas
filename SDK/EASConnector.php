<?php
namespace Gw\EAS\SDK;

use Gw\EAS\Model\DebugLogFactory;
use Gw\EAS\Model\ResourceModel\DebugLog as DebugLogResource;
use Gw\EAS\Model\Config\Source\Environment;
use Gw\EAS\SDK\Requests\CreatePostSaleOrderRequest;
use Gw\EAS\SDK\Requests\ConfirmPostSaleOrderRequest;
use Gw\EAS\SDK\Dto\CreatePostSaleOrderRequest as CreatePostSaleOrderRequestDto;
use Gw\EAS\SDK\Dto\ConfirmPostSaleOrderRequest as ConfirmPostSaleOrderRequestDto;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Connector;
use Saloon\Http\Response;
use Saloon\Traits\OAuth2\ClientCredentialsGrant;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Psr\Http\Message\ResponseInterface;

class EASConnector extends Connector
{
    private const string CACHE_TAG = "GW_EAS_TOKEN_CACHE";

    use ClientCredentialsGrant {
        getAccessToken as protected originalGetAccessToken;
    }

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private CacheInterface $cache,
        private DebugLogFactory $debugLogFactory,
        private DebugLogResource $debugLogResource
    ) {
        if ($this->scopeConfig->isSetFlag(
            'gw_eas/general/debug'
        )) {
            $this->debugResponse(
                function (Response $response, ResponseInterface $psrResponse) {
                    $psrRequest = $response->getPsrRequest();
                    $debug = $this->debugLogFactory->create();
                    $request_body = null;
                    if ($psrRequest->getBody()->getSize()) {
                        $psrRequest->getBody()->rewind();
                        $request_body = $psrRequest->getBody()->getContents();
                        $request_body = json_encode(json_decode($request_body), JSON_PRETTY_PRINT);
                    }
                    $response_body = null;
                    if ($psrResponse->getBody()->getSize()) {
                        $psrResponse->getBody()->rewind();
                        $response_body = $psrResponse->getBody()->getContents();
                        $response_body = json_encode(json_decode($response_body), JSON_PRETTY_PRINT);
                    }
                    $debug->setData('request_headers', print_r($psrRequest->getHeaders(), true));
                    $debug->setData('request_body', $request_body);
                    $debug->setData('request_url', $psrRequest->getUri());
                    $debug->setData('request_method', $psrRequest->getMethod());
                    $debug->setData('response_code', $psrResponse->getStatusCode());
                    $debug->setData('response_body', $response_body);
                    $debug->setData('response_headers', print_r($psrResponse->getHeaders(), true));
                    $this->debugLogResource->save($debug);
                }
            );
        }
    }

    protected function getAccessToken()
    {
        //If the token is in cache, use it
        $data = $this->cache->load(
            self::CACHE_TAG
        );
        if ($data) {
            return AccessTokenAuthenticator::unserialize($data);
        }
        //Get new token
        $accessTokenAuthenticator = $this->originalGetAccessToken();
        //Save token to cache
        $this->cache->save(
            $accessTokenAuthenticator->serialize(),
            self::CACHE_TAG,
            [self::CACHE_TAG],
            $accessTokenAuthenticator->getExpiresAt()->getTimestamp() - time()
        );
        return $accessTokenAuthenticator;
    }

    protected function defaultOauthConfig(): OAuthConfig
    {
        return OAuthConfig::make()
            ->setClientId(
                $this->scopeConfig->getValue(
                    'gw_eas/general/clientkey',
                    ScopeInterface::SCOPE_STORE
                )
            )->setClientSecret(
                $this->scopeConfig->getValue(
                    'gw_eas/general/clientsecret',
                    ScopeInterface::SCOPE_STORE
                )

            )
            ->setDefaultScopes(['openid'])
            ->setTokenEndpoint('/auth/open-id/connect');
    }

    public function resolveBaseUrl(): string
    {
        if ($this->scopeConfig->getValue(
            'autocustomergroup/ukvat/environment',
            ScopeInterface::SCOPE_STORE
        ) === Environment::ENVIRONMENT_SANDBOX) {
            return ("https://internal1.easproject.com/api");
        } else {
            return ("https://manager.easproject.com/api");
        }
    }

    protected function defaultHeaders(): array
    {
        return [
            'user-agent' => 'GrahamWhartonLubeFinder/1.0 graham@lubefinder.com'
        ];
    }

    public function CreatePostSaleOrder(
        CreatePostSaleOrderRequestDto $createPostSaleOrderRequestDto
    ): Response {
        $authenticator = $this->getAccessToken();
        $this->authenticate($authenticator);
        $request = new CreatePostSaleOrderRequest(
            $createPostSaleOrderRequestDto
        );
        return $this->send($request);
    }

    public function ConfirmPostSaleOrder(
        ConfirmPostSaleOrderRequestDto $confirmPostSaleOrderRequestDto
    ): Response {
        $authenticator = $this->getAccessToken();
        $this->authenticate($authenticator);
        $request = new ConfirmPostSaleOrderRequest(
            $confirmPostSaleOrderRequestDto
        );
        return $this->send($request);
    }
}
