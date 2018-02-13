<?php

namespace AppBundle\Validation;

class CsvValidation
{
    // Les en-têtes que doit contenir le fichier CSV
    private static $header = array(
        "tvaintra",
        "raison sociale",
        "adresse",
        "complement d'adresse",
        "code postal",
        "ville",
        "pays",
        "nom d'utilisateur",
        "prenom d'utilisateur",
        "adresse email",
        "numero de telephone",
        "profil utilisateur",
        "siret",
        "DUN",
        "INSEE",
        "gln",
        "accord",
        "langue",
        "type de client",
        "piece jointe",
        "groupe",
        "code client"
    );

    // Les codes ISO des langues vérifiées
    private static $languages = array(
        'fr', 'en', 'de', 'es', 'pt', 'it', 'ar',
        'nl', 'bg', 'lb', 'el', 'hr', 'da', 'et',
        'fi', 'sv', 'hu', 'ga', 'lv', 'lt', 'mt',
        'fy', 'li', 'pl', 'cs', 'ro', 'sk', 'sl'
    );

    private $entreprisesBySiret;
    private $entreprisesBySiren;

    // Les services dont on sert
    private $mailValidation;
    private $sirenValidation;

    public function __construct(MailValidation $mailValidation, SirenValidation $sirenValidation)
    {
        $this->mailValidation = $mailValidation;
        $this->sirenValidation = $sirenValidation;
    }

    /**
     * Cette fonction vérifie le CSV en fonction des tests à effectuer passés en paramètres.
     *
     * Elle remplit deux tableaux $valid et $invalid à partir des lignes du CSV.
     * Une colonne supplémentaire contenant une description de l'erreur est ajoutée au tableau $invalid.
     *
     * @param array $csv
     *      Un tableau à deux dimensions contenant le CSV.
     * @param array $tests
     *      Un tableau contenant les tests à effectuer.
     * @return mixed
     *      Un tableau contenant :
     *          - $result['valid'] contenant le CSV valide
     *          - $result['invalid'] contenant le CSV invalide
     */
    public function checkCsv($csv, $tests)
    {
        // On récupère les données des entreprises si nécessaire
        if ($tests['siret']) {
            $sirets = array();
            foreach ($csv as $row) {
                $sirets[] = $row['siret'];
            }
            $this->entreprisesBySiret = $this->sirenValidation->fetchDataBySiret($sirets);
        }
        if ($tests['tva']) {
            $sirens = array();
            foreach ($csv as $row) {
                $siren = substr($row['tva_intra'], 0, -9);
                $sirens[] = $siren;
            }
            $this->entreprisesBySiren = $this->sirenValidation->fetchDataBySiren($sirens);
        }

        $valid = array();
        $invalid = array();

        foreach ($csv as $row) {
            $error = $this->checkRow($row, $tests);

            // Si on a une erreur
            if ($error != '') {
                // On ajoute la ligne au CSV invalide, avec l'erreur
                $row[] = $error;
                $invalid[] = $row;
            } else // Sinon
            {
                // On ajoute la ligne au tableau valide
                $valid[] = $row;
            }
        }

        $result['valid'] = $valid;
        $result['invalid'] = $invalid;

        return $result;
    }

    /**
     * Cette fonction vérifie une ligne du CSV.
     *
     * @param array $row
     *      Un tableau contenant la ligne à vérifier
     * @param array $tests
     *      Un tableau contenant les tests à effectuer.
     * @return string
     *      Une chaine vide s'il n'y a pas d'erreur, ou une description des erreurs trouvées
     */
    private function checkRow($row, $tests)
    {
        $erreurs = '';

        if ($tests['tva']) {
            if (!$this->isTvaValid($row['tva_intra']))
                $erreurs .= 'siret - ';
        }

        if ($tests['raisonSociale']) {
            if (!$this->isRaisonSocialeValid($row['raison_sociale']))
                $erreurs .= 'raison sociale - ';
        }

        if ($tests['adresse']) {
            if (!$this->isAdresseValid($row['adresse']))
                $erreurs .= 'adresse - ';
        }

        if ($tests['codePostal']) {
            if (!$this->isCodePostalValid($row['code_postal']))
                $erreurs .= 'code postal - ';
        }

        if ($tests['ville']) {
            if (!$this->isVilleValid($row['ville']))
                $erreurs .= 'ville - ';
        }

        if ($tests['email']) {
            if (!$this->isEmailValid($row['email']))
                $erreurs .= 'email - ';
        }

        if ($tests['tel']) {
            if (!$this->isTelValid($row['telephone']))
                $erreurs .= 'téléphone - ';
        }

        if ($tests['profilUtilisateur']) {
            if (!$this->isProfilUtilisateurValid($row['profil']))
                $erreurs .= 'profil utilisateur - ';
        }

        if ($tests['siret']) {
            if (!$this->isSiretValid($row['siret']))
                $erreurs .= 'siret - ';
        }

        if ($tests['accord']) {
            switch ($this->checkAccord($row['accord'])) {
                case 1:
                    if (!$this->isEmailValid($row['email']))
                        $erreurs .= 'email nécessaire - ';
                    break;
                case -1:
                    $erreurs .= 'accord - ';
                    break;
            }
        }

        if ($tests['langue']) {
            if (!$this->isLangueValid($row['langue']))
                $erreurs .= 'téléphone - ';
        }

        if ($tests['typeClient']) {
            if (!$this->isTypeClientValid($row['type_client']))
                $erreurs .= 'type client - ';
        }

        // On retire le dernier tiret
        if ($erreurs !== '')
            $erreurs = substr($erreurs, 0, -3);

        return $erreurs;
    }

    private function isTvaValid($tva)
    {
        if (strlen($tva) === 11) {
            $siren = substr($tva, 0, -9);
            if (array_key_exists($siren, $this->entreprisesBySiren)) {
                if ($this->siren2Tva($siren) === $tva)
                    return true;
                else
                    return false;
            } else
                return false;
        } else
            return false;
    }

    private function isRaisonSocialeValid($raisonSociale)
    {
        return true;
    }

    private function isAdresseValid($adresse)
    {
        return true;
    }

    private function isCodePostalValid($codePostal)
    {
        return true;
    }

    private function isVilleValid($ville)
    {
        return true;
    }

    private function isEmailValid($email)
    {
        if ($this->mailValidation->isValid($email))
            return true;
        else
            return false;
    }

    private function isTelValid($tel)
    {
        if (preg_match("#^((\+33)|0)[1-9]([-\/. ]?[0-9]{2}){4}( +)?$#", $tel)
            || strpos($tel, '_')
            || $tel === '')
            return true;
        else
            return false;
    }

    private function isProfilUtilisateurValid($profilUtilisateur)
    {
        if ($profilUtilisateur === '3' || $profilUtilisateur === '4' || $profilUtilisateur === '5' || $profilUtilisateur === '6')
            return true;
        else
            return false;
    }

    private function isSiretValid($siret)
    {
        return array_key_exists($siret, $this->entreprisesBySiret);
    }

    private function checkAccord($accord)
    {
        if ($accord === 'IF')
            return 0;
        elseif ($accord === 'O')
            return 1;
        else
            return -1;
    }

    private function isLangueValid($langue)
    {
        for ($i = 0; $i < count(self::$languages); $i++) {
            if ($langue === self::$languages[$i])
                break;
        }

        // On est sorti car il n'y avait plus de langue à tester, donc erreur
        if ($i === count(self::$languages))
            return false;
        else
            return true;
    }

    private function isTypeClientValid($typeCLient)
    {
        if ($typeCLient === 'b2b' || $typeCLient === 'b2c')
            return true;
        else
            return false;
    }

    private function siren2Tva($siren)
    {
        $tvaKey = (12 + 3 * ($siren % 97)) % 97;
        $tva = $tvaKey . $siren;

        if (strlen($tva) == 10)
            $tva = '0' . $tva;

        return $tva;
    }
}
