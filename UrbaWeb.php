<?php


namespace AcMarche\UrbaWeb;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class UrbaWeb
{
    use ConnectionTrait;

    private CacheInterface $cache;
    const CODE_CACHE = 'urbaweb2_';

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->cache = Cache::instance();
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
     * Liste des types de permis
     * @return string
     * @throws \Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function typesPermis(): array
    {
        return $this->cache->get(
            self::CODE_CACHE.'typePermis',
            function () {
                $data = $this->requestGet('/ws/type-permis');

                return SortUtils::sortByLibelle($data);
            }
        );
    }

    /**
     * Liste des types de status
     * @return string
     * @throws \Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function typesStatus(): array
    {
        return $this->cache->get(
            self::CODE_CACHE.'typeStatus',
            function () {
                $data = $this->requestGet('/ws/statuts');

                return SortUtils::sortByLibelle($data);
            }
        );
    }

    /**
     * typePermisId (Long) : Id du type de permis
     * typePermisValeur (String) : Code valeur du type de permis
     * numeroPermis (String) : Numéro du permis
     * numeroPermisDelivre (String) : Numéro du permis délivré
     * dateRecepisseDe (String) : Début de la date du récépissé
     * dateRecepisseA (String) : Fin de la date du récépissé
     * statutId (Integer) : ID du type de statut
     * dateStatutDe (String) : Date de début du statut
     * dateStatutA (String) : Date de fin du statut
     * capakey (String) : Capakey de la parcellaire cadastrale
     * capakeyHisto (Boolean) : Historique Capakey
     * @return string
     * @throws \Exception
     */
    public function searchPermis(array $opions = []): array
    {
        $key = implode(',', $opions);

        return $this->cache->get(
            self::CODE_CACHE.'permis_details'.$key,
            fn() => $this->requestGet('/ws/permisIDs/', $opions)
        );
    }

    public function informationsPermis(int $id): \stdClass
    {
        return $this->cache->get(
            self::CODE_CACHE.'permis_details'.$id,
            fn() => $this->requestGet('/ws/permis/'.$id)
        );
    }

    public function searchAdvancePermis(array $opions = [])
    {
        return $this->requestPost('/ws/permisIDs/', $opions);
    }

    /**
     * @throws \Exception
     */
    private function requestGet(string $url, array $options = [])
    {
        try {
            $request = $this->httpClient->request(
                'GET',
                $this->url.$url,
                [
                    'query' => $options,
                ]
            );

            $data = $this->getContent($request);
            var_dump($url);

            return \json_decode($data);
        } catch (TransportExceptionInterface $e) {
            throw  new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    private function requestPost(string $url, array $options = [])
    {
        try {
            $request = $this->httpClient->request(
                'POST',
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

    private function getContent(ResponseInterface $request): string
    {
        try {
            return $request->getContent();
        } catch (ClientExceptionInterface | TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface $e) {
            throw  new \Exception($e->getMessage());
        }
    }
}
