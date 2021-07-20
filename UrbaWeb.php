<?php


namespace AcMarche\UrbaWeb;

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
    public bool $active_cache;

    /**
     * @throws \Exception
     */
    public function __construct(bool $active_cache = true)
    {
        $this->apiRemoteRepository = new ApiRemoteRepository();
        $this->cache               = Cache::instance();
        $this->serializer          = Serializer::create();
        $this->active_cache        = $active_cache;
    }

    public function currentToken(): ?string
    {
        return $this->apiRemoteRepository->currentToken();
    }

    /**
     * Liste des types de permis
     * @return TypePermis[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function typesPermis(): array
    {
        $code = $this->getCode('typePermis');

        return $this->cache->get(
            $code,
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
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function statusPermis(): array
    {
        $code = $this->getCode('typeStatus');

        return $this->cache->get(
            $code,
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
     * @param array $options
     *
     * @return int[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function searchPermis(array $options = []): array
    {
        $key = implode(',', $options);

        $code = $this->getCode('permis_search_'.$key);

        return $this->cache->get(
            $code,
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
     * @param array $options
     *
     * @return int[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function searchAdvancePermis(array $options = []): array
    {
        $key  = implode(',', $options);
        $code = $this->getCode('permis_search_advance'.$key);

        return $this->cache->get(
            $code,
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
        $code = $this->getCode('permis_details_'.$permisId);

        return $this->cache->get(
            $code,
            function () use ($permisId) {
                $responseJson = $this->apiRemoteRepository->requestGet('/ws/permis/'.$permisId);

                if ( ! $responseJson) {
                    return null;
                }

                $t = $this->serializer->deserialize(
                    $responseJson,
                    Permis::class,
                    'json'
                );

                // dd($t);

                return $t;
            }
        );
    }

    public function informationsEnquete(int $permisId): ?Enquete
    {
        $code = $this->getCode('enquete_details_'.$permisId);

        return $this->cache->get(
            $code,
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
        $code = $this->getCode('annonce_details_'.$permisId);

        return $this->cache->get(
            $code,
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
     * @param int $permisId
     *
     * @return array|Demandeur[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function demandeursPermis(int $permisId): array
    {
        $code = $this->getCode('liste_demandeurs'.$permisId);

        return $this->cache->get(
            $code,
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
     * @param int $permisId
     *
     * @return array|Document[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function documentsPermis(int $permisId): array
    {
        $code = $this->getCode('permis_documents_'.$permisId);

        return $this->cache->get(
            $code,
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
        $code = $this->getCode('permis_document_download_'.$documentId);

        return $this->cache->get(
            $code,
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
     * @param Permis $permis
     *
     * @return bool
     */
    public function isPublic(Permis $permis): bool
    {
        $dateFin = $dateDebut = null;

        if ($enquete = $permis->enquete) {
            $dateDebut = $enquete->dateDebutAffichage;
            $dateFin   = $enquete->dateFin;
        }
        if ($annonce = $permis->annonce) {
            $dateDebut = $annonce->dateDebutAffichage;
            $dateFin   = $annonce->dateFinAffichage;
        }

        if ( ! $permis->statut) {
            return false;
        }
        if ($permis->statut->id > 0) {
            return false;
        }


        if ( ! $dateFin && ! $dateDebut) {
            return false;
        }

        $today = new \DateTime();
        /**
         * 2021-07-15
         * ^ "2021-07-26"
         * ^ "2021-07-05"
         */
        if (($today->format('Y-m-d') >= $dateDebut) && ($today->format('Y-m-d') <= $dateFin)) {
            return true;
        }

        return false;
    }

    private function getCode(string $key): string
    {
        if ( ! $this->active_cache) {
            return self::CODE_CACHE.rand(0, 10000).time();
        }

        return self::CODE_CACHE.$key;
    }
}
