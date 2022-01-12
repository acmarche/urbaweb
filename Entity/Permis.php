<?php


namespace AcMarche\UrbaWeb\Entity;


class Permis
{
    public ?int $id = null;
    public ?string $numeroPermis = null;
    public ?string $numeroPermisDelivre = null;
    public ?string $natureTexteLibre = null;
    public ?string $urbain = null;
    public ?TypePermis $typePermis = null;
    public ?string $dateRecepisse = null;
    public ?Nature $nature = null;
    public ?Statut $statut = null;
    public ?TypeStatut $typeStatut = null;
    public ?string $dateStatut = null;
    public ?Adresse $adresseSituation = null;
    public ?Enquete $enquete = null;
    public ?Annonce $annonce = null;
    /**
     * @var array|Demandeur[]
     */
    public array $demandeurs = [];
    /**
     * @var array|Document[]
     */
    public array $documents = [];

    public function dateDebutAffichage(): ?string
    {
        if ($this->enquete !== null) {
            return $this->enquete->dateDebut;
        }
        if ($this->annonce !== null) {
            return $this->annonce->dateDebutAffichage;
        }

        return null;
    }

    public function dateFinAffichage(): ?string
    {
        if ($this->enquete !== null) {
            return $this->enquete->dateFin;
        }
        if ($this->annonce !== null) {
            return $this->annonce->dateFinAffichage;
        }

        return null;
    }
}
