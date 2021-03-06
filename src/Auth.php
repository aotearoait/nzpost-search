<?php


namespace Aotearoait;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class Auth extends Client
{
    const BASE_URI = 'https://oauth.nzpost.co.nz/as/token.oauth2';

    /** @var string */
    protected $client_id;

    /** @var string */
    protected $client_secret;

    /** @var string */
    public $token;

    /**
     * Auth constructor.
     * @param string $client_id
     * @param string $client_secret
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function __construct($client_id, $client_secret)
    {
        parent::__construct();

        $this->client_id = $client_id;
        $this->client_secret = $client_secret;

        $this->setCachedToken();
    }

    /**
     * Set cached token.
     *
     * @throws InvalidArgumentException
     */
    protected function setCachedToken()
    {
        $cache = new FilesystemAdapter();
        $token = $cache->getItem('nz.post.access.token')->get();

        if ($token === null) {
            $token = $cache->get('nz.post.access.token', function (ItemInterface $item) {
                $data = $this->getAccessToken();
                $item->expiresAfter($data->expires_in);
                return $data->access_token;
            });
        }

        $this->token = $token;
    }

    /**
     * Get access token.
     *
     * @return array|bool|float|int|object|string|null
     * @throws GuzzleException
     */
    protected function getAccessToken()
    {
        $response = $this->post(self::BASE_URI, [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => env('NZPOST_CLIENT_ID'),
                'client_secret' => env('NZPOST_CLIENT_SECRET'),
            ],
        ])->getBody()->getContents();
        return \GuzzleHttp\json_decode($response);
    }

    /**
     * Call an endpoint.
     *
     * @param $uri
     * @param null $query
     * @return array|bool|float|int|object|string|null
     * @throws GuzzleException
     */
    protected function call($uri, $query = null)
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ];

        if ($query) {
            $options = array_merge($options, [
                'query' => $query,
            ]);
        }

        $response = $this->get($uri, $options)
            ->getBody()
            ->getContents();

        return \GuzzleHttp\json_decode($response);
    }
}
