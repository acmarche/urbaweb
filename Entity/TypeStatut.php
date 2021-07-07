<?php


namespace AcMarche\UrbaWeb\Entity;


class TypeStatut
{
    public ?int $id = null;
    public ?string $libelle = null;
    public ?string $valeur = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    /**
     * @param string|null $libelle
     */
    public function setLibelle(?string $libelle): void
    {
        $this->libelle = $libelle;
    }

    /**
     * @return string|null
     */
    public function getValeur(): ?string
    {
        return $this->valeur;
    }

    /**
     * @param string|null $valeur
     */
    public function setValeur(?string $valeur): void
    {
        $this->valeur = $valeur;
    }


}
