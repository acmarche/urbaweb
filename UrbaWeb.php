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
     * @return array|TypeStatut[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function statusPermis(): array
    {
        return $this->cache->get(
            self::CODE_CACHE.'typeStatus',
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
        return $this->cache->get(
            self::CODE_CACHE.'permis_details_'.$permisId,
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
        return $this->cache->get(
            self::CODE_CACHE.'enquete_details_'.$permisId,
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

    public function informationsAnnonce(int $permisId): ?Annonce
    {
        return $this->cache->get(
            self::CODE_CACHE.'annonce_details_'.$permisId,
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
        return $this->cache->get(
            self::CODE_CACHE.'liste_demandeur_'.$permisId,
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
        return $this->cache->get(
            self::CODE_CACHE.'permis_documents_'.$permisId,
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
        return $this->cache->get(
            self::CODE_CACHE.'permis_documents_'.$documentId.time(),
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
        $permis->annonce    = $this->informationsAnnonce($permisId);

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
        if ($permis->statut->id > 0) {
            return false;
        }

        $dateFin = $dateDebut = null;

        if ($enquete = $permis->enquete) {
            $dateDebut = $enquete->dateDebutAffichage;
            $dateFin   = $enquete->dateFin;
        }
        if ($annonce = $permis->annonce) {
            $dateDebut = $annonce->dateDebutAffichage;
            $dateFin   = $annonce->dateFinAffichage;
        }
        if ( ! $dateFin && ! $dateDebut) {
            return false;
        }

        $today = new \DateTime();
        if (($today >= $dateDebut) && ($today <= $dateFin)) {
            return true;
        }

        return false;
    }

}
