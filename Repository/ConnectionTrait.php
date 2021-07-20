<?php


namespace AcMarche\UrbaWeb\Repository;

use AcMarche\UrbaWeb\Tools\Env;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\HttpClientInterface;

trait ConnectionTrait
{
    private HttpClientInterface $httpClient;
    private string $code;
    private string $url;
    private string $clef;
    private string $user;
    private string $password;
    private ?string $token;

    public function connect()
    {
        self::loadEnv();
        $this->url      = $_ENV['URBA_URL'];
        $this->user     = $_ENV['URBA_USER'];
        $this->password = $_ENV['URBA_PASSWORD'];

        $options = [

        ];

        $this->httpClient = HttpClient::create($options);
    }

    public function connectWithToken(string $token)
    {
        self::loadEnv();
        $this->url = $_ENV['URBA_URL'];

        $options = new HttpOptions();
        $options->setAuthBearer($token);
        $this->httpClient = HttpClient::createForBaseUri($this->url, $options->toArray());
    }

    private function loadEnv()
    {
        Env::loadEnv();
    }
}
