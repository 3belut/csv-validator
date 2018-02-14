<?php

namespace AppBundle\Validation;

/**
 * Ce service permet la validation des champs du CSV
 */
class CsvValidation
{
    // Les codes ISO des langues vérifiées
    private static $languages = array(
        'fr', 'en', 'de', 'es', 'pt', 'it', 'ar',
        'nl', 'bg', 'lb', 'el', 'hr', 'da', 'et',
        'fi', 'sv', 'hu', 'ga', 'lv', 'lt', 'mt',
        'fy', 'li', 'pl', 'cs', 'ro', 'sk', 'sl'
    );

    // Les tableaux contenant les données des entreprises téléchargées
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
     * Cette fonction vérifie le CSV en appliquant les tests à effectuer passés en paramètres.
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
        if ($tests['siret'] || $tests['raisonSociale'] || $tests['adresse'] || $tests['codePostal'] || $tests['ville']) {
            $sirets = array();
            foreach ($csv as $row) {
                $sirets[] = $row['siret'];
            }
            $this->entreprisesBySiret = $this->sirenValidation->fetchDataBySiret($sirets);
        }
        if ($tests['tva'] || $tests['raisonSociale']) {
            $sirens = array();
            foreach ($csv as $row) {
                $siren = $this->tva2Siren($row['tva_intra']);
                $sirens[] = $siren;
            }
            $this->entreprisesBySiren = $this->sirenValidation->fetchDataBySiren($sirens);
        }

        $valid = array();
        $invalid = array();

        // On vérifie indépendament chaque ligne
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
     *      Un tableau contenant la ligne à vérifier.
     * @param array $tests
     *      Un tableau contenant les tests à effectuer.
     * @return string
     *      Une chaine vide s'il n'y a pas d'erreur, ou une description des erreurs trouvées.
     */
    private function checkRow($row, $tests)
    {
        $erreurs = '';

        if ($tests['tva']) {
            if (!$this->isTvaValid($row['tva_intra']))
                $erreurs .= 'tva - ';
        }

        if ($tests['raisonSociale']) {
            if (!$this->isRaisonSocialeValid($row['raison_sociale'], $this->tva2Siren($row['tva_intra']), $row['siret']))
                $erreurs .= 'raison sociale - ';
        }

        if ($tests['adresse']) {
            if (!$this->isAdresseValid($row['adresse'], $row['siret']))
                $erreurs .= 'adresse - ';
        }

        if ($tests['codePostal']) {
            if (!$this->isCodePostalValid($row['code_postal'], $row['siret']))
                $erreurs .= 'code postal - ';
        }

        if ($tests['ville']) {
            if (!$this->isVilleValid($row['ville'], $row['siret']))
                $erreurs .= 'ville - ';
        }

        if ($tests['email']) {
            if (!$this->mailValidation->isValid($row['email']))
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
                    if (!$this->mailValidation->isValid($row['email']))
                        $erreurs .= 'email nécessaire - ';
                    break;
                case -1:
                    $erreurs .= 'accord - ';
                    break;
            }
        }

        if ($tests['langue']) {
            if (!$this->isLangueValid($row['langue']))
                $erreurs .= 'langue - ';
        }

        if ($tests['typeClient']) {
            if (!$this->isTypeClientValid($row['type_client']))
                $erreurs .= 'type client - ';
        }

        // Si les deux cases TVA et Siret sont cochées et que les deux champs sont remplis, on vérifie la correspondance
        if ($tests['tva'] && $tests['siret'] && $row['tva_intra'] !== '' && $row['siret'] !== '') {
            if (!$this->tvaSiretMatch($row['tva_intra'], $row['siret']))
                $erreurs .= 'correspondance tva/siret';
        }

        // On retire l'éventuel dernier tiret
        if ($erreurs !== '')
            $erreurs = substr($erreurs, 0, -3);

        return $erreurs;
    }

    /**
     * Cette fonction vérifie la validité du numéro de TVA.
     *
     * @param string $tva
     * @return bool
     */
    private function isTvaValid($tva)
    {
        // On vérifie l'existence du SIREN
        $siren = $this->tva2Siren($tva);
        if (array_key_exists($siren, $this->entreprisesBySiren)) {
            // On tente de recalculer le numéro de TVA à partir du SIREN
            if ($this->siren2Tva($siren) === $tva)
                return true;
            else
                return false;
        } else
            return false;
    }

    /**
     * Cette fonction vérifie la validité de la raison sociale d'une entreprise.
     *
     * Elle tente de récupérer la raison sociale de l'entreprise à partir de son SIREN ou son SIRET,
     * puis effectue un test de similarité à 50 %.
     * Seul l'un des deux SIREN ou SIRET peut être fournit. Si les deux son invalides,
     * la fonction ne peut pas vérifier la raison sociale et retourne true.
     *
     * @param string $raisonSociale
     *      La raison sociale à vérifier.
     * @param string $siren
     *      Le SIREN de l'entreprise.
     * @param string $siret
     *      Le SIRET de l'entreprise.
     * @return bool
     */
    private function isRaisonSocialeValid($raisonSociale, $siren, $siret)
    {
        if (array_key_exists($siren, $this->entreprisesBySiren)) {
            similar_text($raisonSociale, $this->entreprisesBySiren[$siren]['raison_sociale'], $percent);
            if ($percent > 50)
                return true;
            else
                return false;
        } elseif (array_key_exists($siret, $this->entreprisesBySiret)) {
            similar_text($raisonSociale, $this->entreprisesBySiret[$siret]['raison_sociale'], $percent);
            if ($percent > 50)
                return true;
            else
                return false;
        } else
            return true;
    }

    /**
     * Cette fonction vérifie la validité de l'adresse d'un établissement.
     *
     * Elle tente de récupérer l'adresse de l'établissement à partir de son SIRET,
     * puis effectue un test de similarité à 80 %. Si le SIRET est invalide,
     * la fonction ne peut pas vérifier l'adresse et retourne true.
     *
     * @param string $adresse
     *      L'adresse à vérifier.
     * @param string $siret
     *      Le SIRET de l'établissement.
     * @return bool
     */
    private function isAdresseValid($adresse, $siret)
    {
        if (array_key_exists($siret, $this->entreprisesBySiret)) {
            similar_text($adresse, $this->entreprisesBySiret[$siret]['adresse'], $percent);
            if ($percent > 80)
                return true;
            else
                return false;
        } else
            return true;
    }

    /**
     * Cette fonction vérifie la validité du code postal d'un établissement.
     *
     * Elle tente de récupérer le code postal de l'établissement à partir de son SIRET,
     * puis le compare avec celui fournit en paramètres. Si le SIRET est invalide,
     * la fonction ne peut pas vérifier le code postal et retourne true.
     *
     * @param string $codePostal
     *      Le code postal à vérifier.
     * @param $siret
     *      Le SIRET de l'établissement.
     * @return bool
     */
    private function isCodePostalValid($codePostal, $siret)
    {
        if (array_key_exists($siret, $this->entreprisesBySiret)) {
            if ($codePostal == $this->entreprisesBySiret[$siret]['code_postal'])
                return true;
            else
                return false;
        } else
            return true;
    }

    /**
     * Cette fonction vérifie la validité de la ville d'un établissement.
     *
     * Elle tente de récupérer la ville de l'établissement à partir de son SIRET,
     * puis effectue un test de similarité à 90 %. Si le SIRET est invalide,
     * la fonction ne peut pas vérifier la ville et retourne true.
     *
     * @param string $ville
     *      La ville à vérifier.
     * @param string $siret
     *      Le SIRET de l'établissement.
     * @return bool
     */
    private function isVilleValid($ville, $siret)
    {
        if (array_key_exists($siret, $this->entreprisesBySiret)) {
            similar_text($ville, $this->entreprisesBySiret[$siret]['ville'], $percent);
            if ($percent > 90)
                return true;
            else
                return false;
        } else
            return true;
    }

    /**
     * Cette vérifie la validité du numéro de téléphone.
     *
     * @param string $tel
     * @return bool
     */
    private function isTelValid($tel)
    {
        if (preg_match("#^((\+33)|0)[1-9]([-\/. ]?[0-9]{2}){4}( +)?$#", $tel)
            || strpos($tel, '_')
            || $tel === '')
            return true;
        else
            return false;
    }

    /**
     * Cette fonction vérifie la validité du profil utilisateur.
     *
     * @param string $profilUtilisateur
     * @return bool
     */
    private function isProfilUtilisateurValid($profilUtilisateur)
    {
        if ($profilUtilisateur === '3' || $profilUtilisateur === '4' || $profilUtilisateur === '5' || $profilUtilisateur === '6')
            return true;
        else
            return false;
    }

    /**
     * Cette fonction vérifie la validité du numéro de SIRET.
     *
     * @param string $siret
     * @return bool
     */
    private function isSiretValid($siret)
    {
        return array_key_exists($siret, $this->entreprisesBySiret);
    }

    /**
     * @param string $accord
     * @return int
     */
    private function checkAccord($accord)
    {
        if ($accord === 'IF')
            return 0;
        elseif ($accord === 'O')
            return 1;
        else
            return -1;
    }

    /**
     * @param string $langue
     * @return bool
     */
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

    /**
     * @param string $typeCLient
     * @return bool
     */
    private function isTypeClientValid($typeCLient)
    {
        if ($typeCLient === 'b2b' || $typeCLient === 'b2c')
            return true;
        else
            return false;
    }

    /**
     * Cette fonction vérifie la correspondance entre un numéro de TVA et un numéro de SIRET.
     *
     * @param string $tva
     * @param string $siret
     * @return bool
     */
    private function tvaSiretMatch($tva, $siret)
    {
        if ($this->tva2Siren($tva) === $this->siret2Siren($siret))
            return true;
        else
            return false;
    }

    /**
     * @param $siret
     * @return bool|string
     */
    private function siret2Siren($siret)
    {
        return substr($siret, 0, 9);
    }

    /**
     * @param $siren
     * @return string
     */
    private function siren2Tva($siren)
    {
        $tvaKey = (12 + 3 * ($siren % 97)) % 97;
        $tva = $tvaKey . $siren;

        if (strlen($tva) === 10)
            $tva = '0' . $tva;

        $tva = 'FR' . $tva;

        return $tva;
    }

    /**
     * @param $tva
     * @return string
     */
    private function tva2Siren($tva)
    {
        $siren = substr($tva, -9);

        return $siren ?: '';
    }
}
