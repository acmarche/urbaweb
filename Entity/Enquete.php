<?php


namespace AcMarche\UrbaWeb\Entity;


class Enquete
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
}
