<?php

namespace AppBundle\Object;

class FileUpload
{
    private $file;

    private $tvaChecked = true;
    private $siretChecked = true;
    private $emailChecked = true;

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

    private $langueChecked = true;
    private $clientChecked = true;
    private $accordChecked = true;
    private $telChecked = false;
    private $codePostalChecked = false;
    private $villeChecked = false;
    private $adresseChecked = false;
    private $raisonSocialeChecked = true;
    private $profilUtilisateurChecked = true;

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
    public function isClientChecked()
    {
        return $this->clientChecked;
    }

    /**
     * @param bool $clientChecked
     */
    public function setClientChecked($clientChecked)
    {
        $this->clientChecked = $clientChecked;
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
}
