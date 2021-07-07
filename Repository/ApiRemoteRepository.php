<?php


namespace AcMarche\UrbaWeb\Repository;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiRemoteRepository
{
    use ConnectionTrait;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->connect();
        if ($this->token = $this->getToken()) {
            $this->connectWithToken($this->token);
        }
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getToken(): ?string
    {
        try {
            $request = $this->httpClient->request(
                'POST',
                $this->url.'/authenticate',
                [
                    'body' =>
                        ['username' => 'bl', 'password' => 'bl'],
                ]
            );

            return $this->getContent($request);
        } catch (TransportExceptionInterface $e) {
            throw  new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function requestGet(string $url, array $options = []): string
    {
        try {
            $request = $this->httpClient->request(
                'GET',
                $this->url.$url,
                [
                    'query' => $options,
                ]
            );

            return $this->getContent($request);

        } catch (TransportExceptionInterface $e) {
            throw  new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function requestPost(string $url, array $parameters = []): string
    {
        try {
            $request = $this->httpClient->request(
                'POST',
                $this->url.$url,
                [
                    'json' => $parameters,
                ]
            );

            return $this->getContent($request);
        } catch (TransportExceptionInterface $e) {
            throw  new \Exception($e->getMessage());
        }
    }

    public function getContent(ResponseInterface $request): string
    {
        try {
            return $request->getContent();
        } catch (ClientExceptionInterface | TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface $e) {
            throw  new \Exception($e->getMessage());
        }
    }
}
