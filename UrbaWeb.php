<?php


namespace AcMarche\UrbaWeb;

use AcMarche\UrbaWeb\Entity\Demandeur;
use AcMarche\UrbaWeb\Entity\Document;
use AcMarche\UrbaWeb\Entity\Permis;
use AcMarche\UrbaWeb\Entity\TypePermis;
use AcMarche\UrbaWeb\Entity\TypeStatut;
use AcMarche\UrbaWeb\Repository\ApiRemoteRepository;
use AcMarche\UrbaWeb\Tools\Cache;
use AcMarche\UrbaWeb\Tools\Serializer;
use AcMarche\UrbaWeb\Tools\SortUtils;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class UrbaWeb
{
    private CacheInterface $cache;
    private const CODE_CACHE = 'urbaweb441_';
    private SerializerInterface $serializer;
    private ApiRemoteRepository $apiRemoteRepository;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->apiRemoteRepository = new ApiRemoteRepository();
        $this->cache               = Cache::instance();
        $this->serializer          = Serializer::create();
    }

    /**
     * Liste des types de permis
     * @return array|TypePermis[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function typesPermis(): array
    {
        return $this->cache->get(
            self::CODE_CACHE.'typePermis',
            function () {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/type-permis');
                $types        = $this->serializer->deserialize(
                    $responseJson,
                    'AcMarche\UrbaWeb\Entity\TypePermis[]',
                    'json'
                );

                return SortUtils::sortByLibelle($types);
            }
        );
    }

    /**
     * Liste des types de status
     * @return array|TypeStatut[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function statusPermis(): array
    {
        return $this->cache->get(
            self::CODE_CACHE.'typeStatus',
            function () {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/statuts');
                $status       = $this->serializer->deserialize(
                    $responseJson,
                    'AcMarche\UrbaWeb\Entity\TypeStatut[]',
                    'json'
                );

                return SortUtils::sortByLibelle($status);
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
     *
     * @param array $options
     *
     * @return array|int[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function searchPermis(array $options = []): array
    {
        $key = implode(',', $options);

        return $this->cache->get(
            self::CODE_CACHE.'permis_search_'.$key,
            function () use ($options) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/permisIDs/', $options);

                return $this->serializer->deserialize(
                    $responseJson,
                    'int[]',
                    'json'
                );
            }
        );
    }

    /**
     * @param array $options
     *
     * @return array|int[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function searchAdvancePermis(array $options = []): array
    {
        $key = implode(',', $options);

        return $this->cache->get(
            self::CODE_CACHE.'permis_search_advance_'.$key,
            function () use ($options) {
                $responseJson = $this->apiRemoteRepository->requestPost('/ws/permisIDs/', $options);

                return $this->serializer->deserialize(
                    $responseJson,
                    'int[]',
                    'json'
                );
            }
        );
    }

    public function informationsPermis(int $id): ?Permis
    {
        return $this->cache->get(
            self::CODE_CACHE.'permis_details_'.$id,
            function () use ($id) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/permis/'.$id);

                return $this->serializer->deserialize(
                    $responseJson,
                    Permis::class,
                    'json'
                );
            }
        );
    }

    public function informationsEnquete(int $id): ?Permis
    {
        return $this->cache->get(
            self::CODE_CACHE.'enquete_details_'.$id,
            function () use ($id) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/enquete/'.$id);

                return $this->serializer->deserialize(
                    $responseJson,
                    Permis::class,
                    'json'
                );
            }
        );
    }

    public function informationsProjet(int $id): ?Permis
    {
        return $this->cache->get(
            self::CODE_CACHE.'projet_details_'.$id,
            function () use ($id) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/annonceProjet/'.$id);

                return $this->serializer->deserialize(
                    $responseJson,
                    Permis::class,
                    'json'
                );
            }
        );
    }

    /**
     * @param int $id
     *
     * @return array|Demandeur[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function listDemandeursPermis(int $id): array
    {
        return $this->cache->get(
            self::CODE_CACHE.'liste_demandeur_'.$id,
            function () use ($id) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/demandeurs/'.$id);

                return $this->serializer->deserialize(
                    $responseJson,
                    'AcMarche\UrbaWeb\Entity\Demandeur[]',
                    'json'
                );
            }
        );
    }

    /**
     * @param int $id
     *
     * @return array|Document[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function documentsPermis(int $id): array
    {
        return $this->cache->get(
            self::CODE_CACHE.'permis_documents_'.$id,
            function () use ($id) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/documents/'.$id);

                return $this->serializer->deserialize(
                    $responseJson,
                    'AcMarche\UrbaWeb\Entity\Document[]',
                    'json'
                );
            }
        );
    }

    public function informationsDocument(int $id): ?Document
    {
        return $this->cache->get(
            self::CODE_CACHE.'permis_documents_'.$id,
            function () use ($id) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/documents/'.$id);

                return $this->serializer->deserialize(
                    $responseJson,
                    Document::class,
                    'json'
                );
            }
        );
    }

}
