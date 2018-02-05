<?php

namespace AppBundle\Object;

class FileUpload
{
    private $file;

    private $tvaChecked = false;
    private $siretChecked = false;
    private $emailChecked = false;
    private $langueChecked = false;
    private $clientChecked = false;
    private $pieceJointeChecked = false;
    private $telChecked = false;
    private $raisonSocialeChecked = false;

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
    public function isPieceJointeChecked()
    {
        return $this->pieceJointeChecked;
    }

    /**
     * @param bool $pieceJointeChecked
     */
    public function setPieceJointeChecked($pieceJointeChecked)
    {
        $this->pieceJointeChecked = $pieceJointeChecked;
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
