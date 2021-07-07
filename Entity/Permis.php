<?php


namespace AcMarche\UrbaWeb\Entity;


class Permis
{
    public ?int $id = null;
    public ?string $numeroPermis = null;
    public ?string $numeroPermisDelivre = null;
    public ?TypePermis $typePermis = null;
    public ?string $dateRecepisse = null;
    public ?Nature $nature = null;
    public ?Statut $statut = null;
    public ?TypeStatut $typeStatut = null;
    public ?string $dateStatut = null;
    public ?Adresse $adresseSituation = null;
    /**
     * @var array|Demandeur[]
     */
    public array $demandeurs = [];
    /**
     * @var array|Document[]
     */
    public array $documents = [];
    public ?Enquete $enquete = null;
}
