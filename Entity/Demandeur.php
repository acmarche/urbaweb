<?php


namespace AcMarche\UrbaWeb\Entity;


class Demandeur
{
    public ?int $id = null;
    public ?string $civilite = null;
    public ?string $nom = null;
    public ?string $prenom = null;
    public ?string $societe = null;
    public ?string $numeroBCE = null;
    public ?string $numeroNational = null;
    public ?Adresse $adresse = null;
    public ?TypeDemandeur $typeDemandeur = null;
}
