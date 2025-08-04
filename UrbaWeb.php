<?php


namespace AcMarche\UrbaWeb;

use Psr\Cache\InvalidArgumentException;
use DateTimeInterface;
use DateTime;
use AcMarche\UrbaWeb\Entity\Annonce;
use AcMarche\UrbaWeb\Entity\Demandeur;
use AcMarche\UrbaWeb\Entity\Document;
use AcMarche\UrbaWeb\Entity\Enquete;
use AcMarche\UrbaWeb\Entity\Permis;
use AcMarche\UrbaWeb\Entity\TypePermis;
use AcMarche\UrbaWeb\Entity\TypeStatut;
use AcMarche\UrbaWeb\Repository\ApiRemoteRepository;
use AcMarche\UrbaWeb\Tools\Cache;
use AcMarche\UrbaWeb\Tools\Serializer;
use AcMarche\UrbaWeb\Tools\SortUtils;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class UrbaWeb
{
    private CacheInterface $cache;
    private const CODE_CACHE = 'urbaweb_';
    private SerializerInterface $serializer;
    private ApiRemoteRepository $apiRemoteRepository;

    public function __construct(public bool $activeCache = true)
    {
        $this->apiRemoteRepository = new ApiRemoteRepository();
        $this->cache               = Cache::instance();
        $this->serializer          = Serializer::create();
    }

    public function currentToken(): ?string
    {
        return $this->apiRemoteRepository->currentToken();
    }

    /**
     * Liste des types de permis
     * @return TypePermis[]
     * @throws InvalidArgumentException
     */
    public function typesPermis(): array
    {
        $cacheKey = $this->getCacheKey('typePermis');

        return $this->cache->get(
            $cacheKey,
            function () {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/type-permis');
                if ( ! $responseJson) {
                    return [];
                }
                $types = $this->serializer->deserialize(
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
     * @return TypeStatut[]
     * @throws InvalidArgumentException
     */
    public function statusPermis(): array
    {
        $cacheKey = $this->getCacheKey('typeStatus');

        return $this->cache->get(
            $cacheKey,
            function () {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/statuts');

                if ( ! $responseJson) {
                    return [];
                }
                $status = $this->serializer->deserialize(
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
     *
     * @return int[]
     * @throws InvalidArgumentException
     */
    public function searchPermis(array $options = []): array
    {
        $key = implode(',', $options);

        $cacheKey = $this->getCacheKey('permis_search_'.$key);

        return $this->cache->get(
            $cacheKey,
            function () use ($options) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/permisIDs/', $options);
                if ( ! $responseJson) {
                    return [];
                }

                return $this->serializer->deserialize(
                    $responseJson,
                    'int[]',
                    'json'
                );
            }
        );
    }

    /**
     *
     * @return int[]
     * @throws InvalidArgumentException
     */
    public function searchAdvancePermis(array $options = []): array
    {
        $key      = implode(',', $options);
        $cacheKey = $this->getCacheKey('permis_search_advance'.$key);

        return $this->cache->get(
            $cacheKey,
            function () use ($options) {
                $responseJson = $this->apiRemoteRepository->requestPost('/ws/permisIDs/', $options);
                if ( ! $responseJson) {
                    return [];
                }

                return $this->serializer->deserialize(
                    $responseJson,
                    'int[]',
                    'json'
                );
            }
        );
    }

    public function informationsPermis(int $permisId): ?Permis
    {
        $cacheKey = $this->getCacheKey('permis_details_'.$permisId);

        return $this->cache->get(
            $cacheKey,
            function () use ($permisId) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/permis/'.$permisId);

                if ( ! $responseJson) {
                    return null;
                }

                return $this->serializer->deserialize(
                    $responseJson,
                    Permis::class,
                    'json'
                );
            }
        );
    }

    public function informationsEnquete(int $permisId): ?Enquete
    {
        $cacheKey = $this->getCacheKey('enquete_details_'.$permisId);

        return $this->cache->get(
            $cacheKey,
            function () use ($permisId) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/enquete/'.$permisId);
                if ( ! $responseJson) {
                    return null;
                }

                return $this->serializer->deserialize(
                    $responseJson,
                    Enquete::class,
                    'json'
                );
            }
        );
    }

    public function informationsAnnonceProjet(int $permisId): ?Annonce
    {
        $cacheKey = $this->getCacheKey('annonce_details_'.$permisId);

        return $this->cache->get(
            $cacheKey,
            function () use ($permisId) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/annonceProjet/'.$permisId);
                if ( ! $responseJson) {
                    return null;
                }

                return $this->serializer->deserialize(
                    $responseJson,
                    Annonce::class,
                    'json'
                );
            }
        );
    }

    /**
     *
     * @return array|Demandeur[]
     * @throws InvalidArgumentException
     */
    public function demandeursPermis(int $permisId): array
    {
        $cacheKey = $this->getCacheKey('liste_demandeurs'.$permisId);

        return $this->cache->get(
            $cacheKey,
            function () use ($permisId) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/demandeurs/'.$permisId);
                if ( ! $responseJson) {
                    return [];
                }

                return $this->serializer->deserialize(
                    $responseJson,
                    'AcMarche\UrbaWeb\Entity\Demandeur[]',
                    'json'
                );
            }
        );
    }

    /**
     *
     * @return array|Document[]
     * @throws InvalidArgumentException
     */
    public function documentsPermis(int $permisId): array
    {
        $cacheKey = $this->getCacheKey('permis_documents_'.$permisId);

        return $this->cache->get(
            $cacheKey,
            function () use ($permisId) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/documents/'.$permisId);
                if ( ! $responseJson) {
                    return [];
                }

                return $this->serializer->deserialize(
                    $responseJson,
                    'AcMarche\UrbaWeb\Entity\Document[]',
                    'json'
                );
            }
        );
    }

    public function downloadDocument(int $documentId): Response
    {
        $cacheKey = $this->getCacheKey('permis_document_download_'.$documentId);

        return $this->cache->get(
            $cacheKey,
            function () use ($documentId) {
                $binary = $this->apiRemoteRepository->requestGet('/ws/document/'.$documentId);

                $response    = new StreamedResponse();
                $disposition = HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_ATTACHMENT,
                    'foo.pdf'
                );

                $response->headers->set('Content-Disposition', $disposition);
                $response->setCallback(
                    function () use ($binary) {
                        echo $binary;
                    }
                );
                $response->send();

                return $response;
            }
        );
    }

    public function fullInformationsPermis(int $permisId): ?Permis
    {
        $permis             = $this->informationsPermis($permisId);
        $permis->demandeurs = $this->demandeursPermis($permisId);
        $permis->documents  = $this->documentsPermis($permisId);
        $permis->enquete    = $this->informationsEnquete($permisId);
        $permis->annonce    = $this->informationsAnnonceProjet($permisId);

        return $permis;
    }

    /**
     * Permis peut être consulté ou pas ?
     * Statut en cours
     * Date d'affichage non dépassée
     *
     *
     */
    public function isPublic(Permis $permis, ?DateTimeInterface $today = null): bool
    {
        $dateFin = $dateDebut = null;

        if (($enquete = $permis->enquete) !== null) {
            $dateDebut = $enquete->dateDebutAffichage;
            $dateFin   = $enquete->dateFin;
        }
        if (($annonce = $permis->annonce) !== null) {
            $dateDebut = $annonce->dateDebutAffichage;
            $dateFin   = $annonce->dateFinAffichage;
        }

        if ( $permis->statut === null) {
            return false;
        }
        if ($permis->statut->id > 0) {
            return false;
        }

        if ( ! $dateFin && ! $dateDebut) {
            return false;
        }

        if ( $today === null) {
            $today = new DateTime();
        }
        return ($today->format('Y-m-d') >= $dateDebut) && ($today->format('Y-m-d') <= $dateFin);
    }

    private function getCacheKey(string $key): string
    {
        if ( ! $this->activeCache) {
            return self::CODE_CACHE.random_int(0, 10000);
        }

        return self::CODE_CACHE.$key;
    }
}
