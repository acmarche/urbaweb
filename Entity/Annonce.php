<?php


namespace AcMarche\UrbaWeb\Entity;


class Annonce
{
    public ?bool $facultative = null;
    public ?string $motifEnquete = null;
    public ?string $dateDebutAffichage = null;
    public ?string $dateEcheanceDebutAffichage = null;
    public ?string $datePremiereConsultation = null;
    public ?string $dateReunion = null;
    public ?string $heureReunion = null;
    public ?string $heureDebut = null;
    public ?string $heureFin = null;
    public ?string $remarque = null;
    public ?string $lieuReunionConcertation = null;
    public ?string $autresConsultations = null;
    public ?string $lieuxAffichage = null;
    public ?string $dateEcheanceDebutReclamations = null;
    public ?string $dateDebutReclamations = null;
    public ?string $dateEcheanceFinReclamations = null;
    public ?string $dateFinReclamations = null;
    public ?string $dateEcheanceFinAffichage = null;
    public ?string $dateFinAffichage = null;
}
