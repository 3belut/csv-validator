<?php

namespace AppBundle\Controller;

use AppBundle\Object\Csv;
use AppBundle\Object\FileUpload;
use AppBundle\Validation\MailValidation;
use AppBundle\Validation\SirenValidation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HomeController extends Controller
{
    public $languages = array('fr', 'en', 'de', 'es', 'pt', 'it', 'ar', 'nl',
        'bg', 'lb', 'el', 'hr', 'da', 'et', 'fi', 'sv', 'hu', 'ga', 'lv', 'lt',
        'mt', 'fy', 'li', 'pl', 'cs', 'ro', 'sk', 'sl');
    public $conformiteSiret = true;

    /**
     * Cette fonction retourne la page d'accueil permettant d'uploader le CSV et de cocher les tests souhaités.
     *
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request, SessionInterface $session)
    {
        // Création du formulaire
        $fileUpload = new FileUpload();
        $form = $this->get('form.factory')->createBuilder(FormType::class, $fileUpload)
            ->add('tvaChecked', CheckboxType::class, array(
                'label' => 'TVA intracommunautaire',
                'required' => false
            ))
            ->add('siretChecked', CheckboxType::class, array(
                'label' => 'Siret',
                'required' => false
            ))
            ->add('emailChecked', CheckboxType::class, array(
                'label' => 'Email',
                'required' => false
            ))
            ->add('langueChecked', CheckboxType::class, array(
                'label' => 'Langue',
                'required' => false
            ))
            ->add('clientChecked', CheckboxType::class, array(
                'label' => 'Type client',
                'required' => false
            ))
            ->add('accordChecked', CheckboxType::class, array(
                'label' => 'Accord',
                'required' => false
            ))
            ->add('telChecked', CheckboxType::class, array(
                'label' => 'Numéro de télephone',
                'required' => false

            ))
            ->add('raisonSocialeChecked', CheckboxType::class, array(
                'label' => 'Raison Sociale',
                'required' => false
            ))
            ->add('profilUtilisateurChecked', CheckboxType::class, array(
                'label' => 'Profil utilisateur',
                'required' => false
            ))
            ->add('file', FileType::class)
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // On charge le fichier CSV dans la classe Csv
            $csv = new Csv();
            $csv->lire_csv($fileUpload->getFile()->getPathname());

            // On stocke le CSV dans la session
            $session->set('csv', serialize($csv));
            $session->set('tva', $fileUpload->isTvaChecked());
            $session->set('siret', $fileUpload->isSiretChecked());
            $session->set('email', $fileUpload->isEmailChecked());
            $session->set('langue', $fileUpload->isLangueChecked());
            $session->set('client', $fileUpload->isClientChecked());
            $session->set('accord', $fileUpload->isAccordChecked());
            $session->set('tel', $fileUpload->isTelChecked());
            $session->set('raison_sociale', $fileUpload->isRaisonSocialeChecked());
            $session->set('profil_utilisateur', $fileUpload->isProfilUtilisateurChecked());

            // On retourne la page contenant la barre de progression
            return $this->render('running.html.twig');
        }

        // On retourne le formulaire
        return $this->render('home.html.twig', array('form' => $form->createView()));
    }

    /**
     * Cette fonction contient le script de vérification du fichier CSV.
     *
     * @Route("/running", name="running")
     */
    public function runningAction(SessionInterface $session)
    {
        $csv = unserialize($session->get('csv'));
        $tva = $session->get('tva');
        $siret = $session->get('siret');
        $email = $session->get('email');
        $langue = $session->get('langue');
        $client = $session->get('client');
        $accord = $session->get('accord');
        $tel = $session->get('tel');
        $raison_sociale = $session->get('raison_sociale');
        $profil_utilisateur = $session->get('profil_utilisateur');

        $indiceWrongSiret = array();
        $indiceTrueSiret = array();
        $sirenValidation = new SirenValidation();
        $listSiret = array();
        $resultAPI = array();

        if ($csv->verif_header()) {
            $needEmail = false;
            //echo "on passe le header";
            //début des vérifications classées par ordre de complexité
            $indiceSiret2Check = array();
            for ($i = 0; $i < $csv->getSize(); $i++) {
                $valideRow = true;

                //*************************VERIF PIECE JOINTE*************************************\\
                if ($accord && $valideRow) {
                    if (strcmp(($csv->getContent())[$i]['accord'], 'O') == 0 || strcmp(($csv->getContent())[$i]['accord'], 'IF') == 0) {
                        //TODO pièce jointe valide
                        echo "<br>" . "accord valide" . "<br>";
                        if (strcmp(($csv->getContent())[$i]['accord'], 'O') == 0) {
                            $needEmail = true;
                        }
                    } else {
                        $valideRow = false;
                        //TODO pièce jointe invalide
                        echo "<br>" . "accord invalide" . "<br>";
                    }
                }
                //********************VERIF TYPE CLIENT********************************************\\
                if ($client && $valideRow) {
                    if (strcmp(($csv->getContent())[$i]['type_client'], 'b2b') == 0 || strcmp(($csv->getContent())[$i]['type_client'], 'b2c') == 0) {
                        //TODO type client valide
                        echo "<br>" . "type client valide" . "<br>";
                    } else {
                        $valideRow = false;
                        //TODO type client invalide
                        echo "<br>" . "type client invalide" . "<br>";
                    }
                }
                //********************VERIF PROFIL UTILISATEUR********************************\\
                if ($profil_utilisateur && $valideRow) {
                    if (strcmp(($csv->getContent())[$i]['profil'], "3") == 0
                        || strcmp(($csv->getContent())[$i]['profil'], "4") == 0
                        || strcmp(($csv->getContent())[$i]['profil'], "5") == 0
                        || strcmp(($csv->getContent())[$i]['profil'], "6") == 0) {
                        //TODO profil utilisateur valide
                        echo "<br>" . "profil utilisateur valide" . "<br>";
                    } else {
                        $valideRow = false;
                        echo "<br>" . "profil utilisateur invalide" . "<br>";
                        //TODO profil utilisatuer invalide
                    }
                }
                //*******************VERIF NUMERO TEL*************************************\\
                if ($tel && $valideRow) {
                    if (preg_match("#^((\+33)|0)[1-9]([-\/. ]?[0-9]{2}){4}( +)?$#", ($csv->getContent())[$i]['telephone']) == true
                        || strpos(($csv->getContent())[$i]['telephone'], '_') !== false
                        || strcmp(($csv->getContent())[$i]['telephone'], "") == 0) {
                        //TODO telephone valide
                        echo "<br>" . "num tel valide" . "<br>";
                    } else {
                        $valideRow = false;
                        //TODO telephone invalide
                        echo "<br>" . "num tel invalide" . "<br>";
                    }
                }
                //*******************VERIF LANGUE**************************************\\
                if ($langue && $valideRow) {
                    $find = false;
                    $j = 0;
                    while ($j < count($this->languages) && $find == false) {
                        if (strcmp(rtrim(($csv->getContent())[$i]['langue']), $this->languages[$j]) == 0) {
                            $find = true;
                        }
                        $j++;
                    }
                    if ($find) {
                        //TODO lanque valide
                        echo "<br>" . "Langue valide" . "<br>";

                    } else {
                        $valideRow = false;
                        //TODO langue invalide
                        echo "<br>" . "Langue invalide" . "<br>";
                    }
                }
                //********************VERIF EMAIL************************************\\
                if ($email && $valideRow) {
                    $mailValidation = new MailValidation();
                    if ($needEmail) {
                        if (strcmp(($csv->getContent())[$i]['email'], "") == 0) {    // est vide
                            $valideRow = false;
                            echo "<br>" . "email invalide car manquant" . "<br>";
                            //TODO email invalide car manquant et indispensable
                        } else {
                            if ($mailValidation->isValid(($csv->getContent())[$i]['email'])) {
                                echo "<br>" . "email valide" . "<br>";
                                //TODO email valide!!
                            } else {
                                echo "<br>" . "email invalide" . "<br>";
                                $valideRow = false;
                                //TODO email invalide!!
                            }
                        }
                    } else {
                        if ($mailValidation->isValid(($csv->getContent())[$i]['email'])) {
                            //TODO email valide!!
                            echo "<br>" . "email valide" . "<br>";
                        } else {
                            $valideRow = false;
                            //TODO email invalide!!
                            echo "<br>" . "email invalide" . "<br>";
                        }
                    }
                }

                //*********************VERIF SIRET*************************************************\\
                if ($siret && $valideRow) {
                    $siretCsv = ($csv->getContent())[$i]['siret'];
                    if (strlen($siretCsv) == 14 && $this->conformiteSiret) {
                        if (strpos($siretCsv, 'E') === false || strpos($siretCsv, '+') === false) {
                            //TODO check Siret et vérifier exposant;

                            array_push($indiceSiret2Check, "$i => $siretCsv");

                            $siren = substr($siretCsv, 0, -5);
                            $key = $this->sirenToTVAKey($siren);
                            $calculatedTVA = "FR" . $key . $siren;
                        } else {
                            $this->conformiteSiret = false;
                            $valideRow = false;
                        }
                    } else {
                        //TODO erreur Siret non conforme
                        array_push($indiceWrongSiret, $i);
                    }
                }
                //**********************VERIF RAISON SOCIALE******************************************\\
                if ($raison_sociale && $valideRow) {
                    //TODO check raison sociale
                }
                //*********************VERIF TVA***************************************\\
                if ($tva && $valideRow) {
                    //TODO check TVA;
                }
            }
        } else {
            //TODO erreur en-tête non conforme!!
        }


        if (count($indiceSiret2Check) >= 1) {
            for ($n = 0; $n < count(array_keys($indiceSiret2Check)); $n++) {
                array_push($listSiret, ($csv->getContent())[array_keys($indiceSiret2Check)[$n]]['siret']);
            }
            $resultAPI = $sirenValidation->fetchDataBySiret($listSiret);

            $wrongSirets = array_diff($listSiret, array_keys($resultAPI));

            for ($m = 0; $m < count($wrongSirets); $m++) {
                $key = array_keys($wrongSirets);
                array_push($indiceWrongSiret, "$key[$m]");
            }
            $allKeys = array_keys($indiceSiret2Check);
            $indiceTrueSiret = array_diff($allKeys, $indiceWrongSiret);

        }
        return new Response("running");
    }

    /**
     * Cette fonction retourne la page de résultats.
     *
     * @Route("/results", name="results")
     */
    public function resultsAction(SessionInterface $session)
    {
        return new Response("results");
    }

    /**
     * Cette fonction retourne l'état de progression du traitement du fichier CSV,
     * afin de mettre à jour l'avancement de la barre de progression.
     *
     * @Route("/progress", name="progress")
     */
    public function getProgressAction(SessionInterface $session)
    {
        if ($session->has('progress')) {
            // On incrémente la valeur pour les tests
            $progress = $session->get('progress');
            if ($progress < 100)
                $progress++;
            else
                $progress = 0;
        } else {
            $progress = 0;
        }
        $session->set('progress', $progress);
        return new Response(json_encode($progress));
    }


    public function sirenToTVAKey($siren)
    {
        return (12 + 3 * ($siren % 97)) % 97;
    }

    public function verifISO($iso)
    {
        if (strlen($iso) == 2) {
            $nb = count($this->languages);
            $i = 0;
            $test = false;
            while ($i < $nb && !$test) {
                if (strcmp($iso, $this->languages[$i]) == 0)
                    $test = true;
                $i++;
            }
            if ($test)
                return true;
            else
                return false;
        } else
            return false;
    }
}
