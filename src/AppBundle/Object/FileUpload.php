<?php

namespace AppBundle\Object;

class FileUpload
{
    private $tvaChecked = true;
    private $raisonSocialeChecked = true;
    private $adresseChecked = false;
    private $codePostalChecked = false;
    private $villeChecked = false;
    private $emailChecked = true;
    private $telChecked = false;
    private $profilUtilisateurChecked = true;
    private $siretChecked = true;
    private $accordChecked = true;
    private $langueChecked = true;
    private $typeClientChecked = true;
    private $replaceTva = false;
    private $replaceCoordonnees = false;

    /**
     * @return bool
     */
    public function isReplaceTva()
    {
        return $this->replaceTva;
    }

    /**
     * @param bool $replaceTva
     */
    public function setReplaceTva($replaceTva)
    {
        $this->replaceTva = $replaceTva;
    }

    /**
     * @return bool
     */
    public function isReplaceCoordonnees()
    {
        return $this->replaceCoordonnees;
    }

    /**
     * @param bool $replaceCoordonnees
     */
    public function setReplaceCoordonnees($replaceCoordonnees)
    {
        $this->replaceCoordonnees = $replaceCoordonnees;
    }

    private $file;

    /**
     * @return bool
     */
    public function isTvaChecked()
    {
        return $this->tvaChecked;
    }

    /**
     * @param bool $tvaChecked
     */
    public function setTvaChecked($tvaChecked)
    {
        $this->tvaChecked = $tvaChecked;
    }

    /**
     * @return bool
     */
    public function isRaisonSocialeChecked()
    {
        return $this->raisonSocialeChecked;
    }

    /**
     * @param bool $raisonSocialeChecked
     */
    public function setRaisonSocialeChecked($raisonSocialeChecked)
    {
        $this->raisonSocialeChecked = $raisonSocialeChecked;
    }

    /**
     * @return bool
     */
    public function isAdresseChecked()
    {
        return $this->adresseChecked;
    }

    /**
     * @param bool $adresseChecked
     */
    public function setAdresseChecked($adresseChecked)
    {
        $this->adresseChecked = $adresseChecked;
    }

    /**
     * @return bool
     */
    public function isCodePostalChecked()
    {
        return $this->codePostalChecked;
    }

    /**
     * @param bool $codePostalChecked
     */
    public function setCodePostalChecked($codePostalChecked)
    {
        $this->codePostalChecked = $codePostalChecked;
    }

    /**
     * @return bool
     */
    public function isVilleChecked()
    {
        return $this->villeChecked;
    }

    /**
     * @param bool $villeChecked
     */
    public function setVilleChecked($villeChecked)
    {
        $this->villeChecked = $villeChecked;
    }

    /**
     * @return bool
     */
    public function isEmailChecked()
    {
        return $this->emailChecked;
    }

    /**
     * @param bool $emailChecked
     */
    public function setEmailChecked($emailChecked)
    {
        $this->emailChecked = $emailChecked;
    }

    /**
     * @return bool
     */
    public function isTelChecked()
    {
        return $this->telChecked;
    }

    /**
     * @param bool $telChecked
     */
    public function setTelChecked($telChecked)
    {
        $this->telChecked = $telChecked;
    }

    /**
     * @return bool
     */
    public function isProfilUtilisateurChecked()
    {
        return $this->profilUtilisateurChecked;
    }

    /**
     * @param bool $profilUtilisateurChecked
     */
    public function setProfilUtilisateurChecked($profilUtilisateurChecked)
    {
        $this->profilUtilisateurChecked = $profilUtilisateurChecked;
    }

    /**
     * @return bool
     */
    public function isSiretChecked()
    {
        return $this->siretChecked;
    }

    /**
     * @param bool $siretChecked
     */
    public function setSiretChecked($siretChecked)
    {
        $this->siretChecked = $siretChecked;
    }

    /**
     * @return bool
     */
    public function isAccordChecked()
    {
        return $this->accordChecked;
    }

    /**
     * @param bool $accordChecked
     */
    public function setAccordChecked($accordChecked)
    {
        $this->accordChecked = $accordChecked;
    }

    /**
     * @return bool
     */
    public function isLangueChecked()
    {
        return $this->langueChecked;
    }

    /**
     * @param bool $langueChecked
     */
    public function setLangueChecked($langueChecked)
    {
        $this->langueChecked = $langueChecked;
    }

    /**
     * @return bool
     */
    public function isTypeClientChecked()
    {
        return $this->typeClientChecked;
    }

    /**
     * @param bool $typeClientChecked
     */
    public function setTypeClientChecked($typeClientChecked)
    {
        $this->typeClientChecked = $typeClientChecked;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }
}
