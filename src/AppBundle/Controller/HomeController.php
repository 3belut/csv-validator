<?php

namespace AppBundle\Controller;

use AppBundle\Object\Csv;
use AppBundle\Object\FileUpload;
use AppBundle\Validation\MailValidation;
use AppBundle\Validation\SirenValidation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use function Symfony\Component\Debug\Tests\testHeader;
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
    private $conformiteSiret = true;
    private $siretOrTva = true;
    private $trueLine = true;
    private $indiceWrongLigne = array();
    private $indiceGoodLigne = array();
    private $indiceLigne2Check = array();
    private $codeErreur = array();
    private $indiceSiret2Check = array();
    private $indiceTVA2Check = array();

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
            ->add('codePostalChecked', CheckboxType::class, array(
                'label' => 'Code postal',
                'required' => false
            ))
            ->add('villeChecked', CheckboxType::class, array(
                'label' => 'Ville',
                'required' => false
            ))
            ->add('adresseChecked', CheckboxType::class, array(
                'label' => 'Adresse',
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
            if ($fileUpload->isRaisonSocialeChecked()) {
                if ($fileUpload->isSiretChecked() == false && $fileUpload->isTvaChecked() == false) {
                    $fileUpload->setSiretChecked(true);
                }
                if ($fileUpload->isTvaChecked() == true) {
                    $this->siretOrTva = false;
                }
            }


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
            $session->set('siretOrTva', $this->siretOrTva);

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
        $SIRETORTVA = $session->get('siretOrTva');

        $sirenValidation = new SirenValidation();
        $listSiret = array();
        $listSiren = array();
        $resultAPI = array();
        $wrongSirets = array();

        if ($csv->verif_header()) {
            $needEmail = false;

            //début des vérifications classées par ordre de complexité

            for ($i = 0; $i < $csv->getSize(); $i++) {
                $this->trueLine = true;
                //*************************VERIF ACCORD*************************************\\
                if ($accord) {
                    if (strcmp(($csv->getContent())[$i]['accord'], 'O') == 0 || strcmp(($csv->getContent())[$i]['accord'], 'IF') == 0) {
                        if (strcmp(($csv->getContent())[$i]['accord'], 'O') == 0) {
                            $needEmail = true;
                        }
                        if (!in_array($i, $this->indiceGoodLigne) && $this->trueLine) {
                            array_push($this->indiceGoodLigne, $i);
                        }
                    } else {
                        if (!array_key_exists($i, $this->codeErreur)) {
                            $this->codeErreur[$i] = "Accord";
                        } else {
                            $this->codeErreur[$i] = $this->codeErreur[$i] . ", Accord";
                        }
                        if ($this->trueLine) {
                            if (in_array($i, $this->indiceGoodLigne)) {
                                unset($this->indiceGoodLigne[$i]);
                            }
                            array_push($this->indiceWrongLigne, $i);
                        }
                        $this->trueLine = false;
                    }
                }
                //********************VERIF TYPE CLIENT********************************************\\
                if ($client) {
                    if (strcmp(($csv->getContent())[$i]['type_client'], 'b2b') == 0 || strcmp(($csv->getContent())[$i]['type_client'], 'b2c') == 0) {
                        if (!in_array($i, $this->indiceGoodLigne) && $this->trueLine) {
                            array_push($this->indiceGoodLigne, $i);
                        }
                    } else {
                        if (!array_key_exists($i, $this->codeErreur)) {
                            $this->codeErreur[$i] = "Type client";
                        } else {
                            $this->codeErreur[$i] = $this->codeErreur[$i] . ", Type client";
                        }
                        if ($this->trueLine) {
                            if (in_array($i, $this->indiceGoodLigne)) {
                                unset($this->indiceGoodLigne[$i]);
                            }
                            array_push($this->indiceWrongLigne, $i);
                        }
                        $this->trueLine = false;
                    }
                }
                //********************VERIF PROFIL UTILISATEUR********************************\\
                if ($profil_utilisateur) {
                    if (strcmp(($csv->getContent())[$i]['profil'], "3") == 0
                        || strcmp(($csv->getContent())[$i]['profil'], "4") == 0
                        || strcmp(($csv->getContent())[$i]['profil'], "5") == 0
                        || strcmp(($csv->getContent())[$i]['profil'], "6") == 0) {
                        if (!in_array($i, $this->indiceGoodLigne) && $this->trueLine) {
                            array_push($this->indiceGoodLigne, $i);
                        }
                    } else {
                        if (!array_key_exists($i, $this->codeErreur)) {
                            $this->codeErreur[$i] = "Profil utilisateur";
                        } else {
                            $this->codeErreur[$i] = $this->codeErreur[$i] . ", Profil utilisateur";
                        }
                        if ($this->trueLine) {
                            if (in_array($i, $this->indiceGoodLigne)) {
                                unset($this->indiceGoodLigne[$i]);
                            }
                            array_push($this->indiceWrongLigne, $i);
                        }
                        $this->trueLine = false;
                    }
                }
                //*******************VERIF NUMERO TEL*************************************\\
                if ($tel) {
                    if (preg_match("#^((\+33)|0)[1-9]([-\/. ]?[0-9]{2}){4}( +)?$#", ($csv->getContent())[$i]['telephone']) == true
                        || strpos(($csv->getContent())[$i]['telephone'], '_') !== false
                        || strcmp(($csv->getContent())[$i]['telephone'], "") == 0) {
                        if (!in_array($i, $this->indiceGoodLigne) && $this->trueLine) {
                            array_push($this->indiceGoodLigne, $i);
                        }
                    } else {
                        if (!array_key_exists($i, $this->codeErreur)) {
                            $this->codeErreur[$i] = "Téléphone";
                        } else {
                            $this->codeErreur[$i] = $this->codeErreur[$i] . ", Téléphone";
                        }
                        if ($this->trueLine) {
                            if (in_array($i, $this->indiceGoodLigne)) {
                                unset($this->indiceGoodLigne[$i]);
                            }
                            array_push($this->indiceWrongLigne, $i);
                        }
                        $this->trueLine = false;
                    }
                }
                //*******************VERIF LANGUE**************************************\\
                if ($langue) {
                    $find = false;
                    $j = 0;
                    while ($j < count($this->languages) && $find == false) {
                        if (strcmp(rtrim(($csv->getContent())[$i]['langue']), $this->languages[$j]) == 0) {
                            $find = true;
                        }
                        $j++;
                    }
                    if ($find) {
                        if (!in_array($i, $this->indiceGoodLigne) && $this->trueLine) {
                            array_push($this->indiceGoodLigne, $i);
                        }
                    } else {
                        if (!array_key_exists($i, $this->codeErreur)) {
                            $this->codeErreur[$i] = "Langue";
                        } else {
                            $this->codeErreur[$i] = $this->codeErreur[$i] . ", Langue";
                        }
                        if ($this->trueLine) {
                            if (in_array($i, $this->indiceGoodLigne)) {
                                unset($this->indiceGoodLigne[$i]);
                            }
                            array_push($this->indiceWrongLigne, $i);
                        }
                        $this->trueLine = false;
                    }
                }
                //********************VERIF EMAIL************************************\\
                if ($email) {
                    $mailValidation = new MailValidation();
                    if ($needEmail) {
                        if (strcmp(($csv->getContent())[$i]['email'], "") == 0) {    // est vide
                            if (!array_key_exists($i, $this->codeErreur)) {
                                $this->codeErreur[$i] = "Email indispensable";
                            } else {
                                $this->codeErreur[$i] = $this->codeErreur[$i] . ", Email indispensable";
                            }
                            if ($this->trueLine) {
                                if (in_array($i, $this->indiceGoodLigne)) {
                                    unset($this->indiceGoodLigne[$i]);
                                }
                                array_push($this->indiceWrongLigne, $i);
                            }
                            $this->trueLine = false;
                        } else {
                            if ($mailValidation->isValid(($csv->getContent())[$i]['email'])) {
                                if (!in_array($i, $this->indiceGoodLigne) && $this->trueLine) {
                                    array_push($this->indiceGoodLigne, $i);
                                }
                            } else {
                                if (!array_key_exists($i, $this->codeErreur)) {
                                    $this->codeErreur[$i] = "Email";
                                } else {
                                    $this->codeErreur[$i] = $this->codeErreur[$i] . ", Email";
                                }
                                if ($this->trueLine) {
                                    if (in_array($i, $this->indiceGoodLigne)) {
                                        unset($this->indiceGoodLigne[$i]);
                                    }
                                    array_push($this->indiceWrongLigne, $i);
                                }
                                $this->trueLine = false;
                            }
                        }
                    } else {
                        if ($mailValidation->isValid(($csv->getContent())[$i]['email'])) {
                            if (!in_array($i, $this->indiceGoodLigne) && $this->trueLine) {
                                array_push($this->indiceGoodLigne, $i);
                            }
                        } else {
                            if (!array_key_exists($i, $this->codeErreur)) {
                                $this->codeErreur[$i] = "Email";
                            } else {
                                $this->codeErreur[$i] = $this->codeErreur[$i] . ", Email";
                            }
                            if ($this->trueLine) {
                                if (in_array($i, $this->indiceGoodLigne)) {
                                    unset($this->indiceGoodLigne[$i]);
                                }
                                array_push($this->indiceWrongLigne, $i);
                            }
                            $this->trueLine = false;
                        }
                    }
                }
                //**********************VERIF RAISON SOCIALE******************************************\\
                if ($raison_sociale) {
                    if (strcmp(($csv->getContent())[$i]['raison_sociale'], "") == 0) {
                        if (!array_key_exists($i, $this->codeErreur)) {
                            $this->codeErreur[$i] = "Raison sociale";
                        } else {
                            $this->codeErreur[$i] = $this->codeErreur[$i] . ", Raison sociale";
                        }
                        if ($this->trueLine) {
                            if (in_array($i, $this->indiceGoodLigne)) {
                                unset($this->indiceGoodLigne[$i]);
                            }
                            array_push($this->indiceWrongLigne, $i);
                        }
                        $this->trueLine = false;
                    }
                }
                //*********************VERIF SIRET*************************************************\\
                if ($siret) {
                    $this->verifSiret($csv, $i);
                }

                //*********************VERIF TVA***************************************\\
                if ($tva) {
                    $TVA = ($csv->getContent())[$i]['tva_intra'];
                    if (strcmp($TVA, "") != 0) {
                        $this->indiceTVA2Check[$i] = $TVA;
                    } else {
                        if (!array_key_exists($i, $this->codeErreur)) {
                            $this->codeErreur[$i] = "Tva";
                        } else {
                            $this->codeErreur[$i] = $this->codeErreur[$i] . ", Tva";
                        }
                        if ($this->trueLine) {
                            if (in_array($i, $this->indiceGoodLigne)) {
                                unset($this->indiceGoodLigne[$i]);
                            }
                            array_push($this->indiceWrongLigne, $i);
                        }
                        $this->trueLine = false;
                    }
                }
            }
        } else {
            $this->codeErreur[0] = "En tête non conforme";
        }

        //********************VERIFICATION DU SIRET VIA UN ENVOIE GROUPE A L'API*********\\
        if (count($this->indiceSiret2Check) >= 1) {
            for ($n = 0; $n < count(array_keys($this->indiceSiret2Check)); $n++) {
                array_push($listSiret, ($csv->getContent())[array_keys($this->indiceSiret2Check)[$n]]['siret']);
            }
            $resultAPI = $sirenValidation->fetchDataBySiret($listSiret);
            $wrongSirets = array_diff($listSiret, array_keys($resultAPI));

            for ($m = 0; $m < count($wrongSirets); $m++) {
                $key = array_keys($wrongSirets);
                if (!array_key_exists($key[$m], $this->codeErreur)) {
                    $this->codeErreur[$key[$m]] = "Tva";
                } else {
                    $this->codeErreur[$key[$m]] = $this->codeErreur[$key[$m]] . ", Tva";
                }
                if (!in_array($key[$m], $this->indiceWrongLigne)) {
                    if (in_array($key[$m], $this->indiceGoodLigne)) {
                        unset($this->indiceGoodLigne[$key[$m]]);
                    }
                    array_push($this->indiceWrongLigne, $key[$m]);
                }
            }
            $allKeys = array_keys($this->indiceSiret2Check);
            $this->indiceLigne2Check = array_diff($allKeys, $this->indiceWrongLigne);
            for ($m = 0; $m < count($this->indiceLigne2Check); $m++) {
                if (!in_array(array_keys($this->indiceLigne2Check)[$m], $this->indiceGoodLigne)) {
                    array_push($this->indiceGoodLigne, array_values($this->indiceLigne2Check)[$m]);
                }
            }
        }

        //******************VERIFICATION DE LA TVA INTRA VIA UN ENVOIE GROUPE A L'API**********\\
        if (count($this->indiceTVA2Check) >= 1 && $siret == false) {
            for ($n = 0; $n < count(array_keys($this->indiceTVA2Check)); $n++) {
                $tvaCsv = ($csv->getContent())[array_keys($this->indiceTVA2Check)[$n]]['tva_intra'];
                $siren = substr($tvaCsv, 4);
                array_push($listSiren, $siren);
            }
            $resultAPISiren = $sirenValidation->fetchDataBySiren($listSiren);
            $wrongSirens = array_diff($listSiren, array_keys($resultAPISiren));
            for ($m = 0; $m < count($wrongSirens); $m++) {
                $key = array_keys($wrongSirens);
                if (!array_key_exists($key[$m], $this->codeErreur)) {
                    $this->codeErreur[$key[$m]] = "Tva";
                } else {
                    $this->codeErreur[$key[$m]] = $this->codeErreur[$key[$m]] . ", Tva";
                }
                if (!in_array($key[$m], $this->indiceWrongLigne)) {
                    if (in_array($key[$m], $this->indiceGoodLigne)) {
                        unset($this->indiceGoodLigne[$key[$m]]);
                    }
                    array_push($this->indiceWrongLigne, $key[$m]);
                }
            }
            $allKeys = array_keys($this->indiceTVA2Check);
            $this->indiceLigne2Check = array_diff($allKeys, $this->indiceWrongLigne);
            for ($m = 0; $m < count(array_diff($listSiren, $wrongSirens)); $m++) {
                $sirenAPI = array_values(array_diff($listSiren, $wrongSirens))[$m];
                $calculatedkey = $this->sirenToTVAKey($sirenAPI);
                $calculatedTVA = "FR" . $calculatedkey . $sirenAPI;
                $tvaCsv = ($csv->getContent())[array_values($this->indiceLigne2Check)[$m]]['tva_intra'];
                if (strcmp($tvaCsv, $calculatedTVA) != 0) {
                    array_push($this->indiceWrongLigne, array_values($this->indiceLigne2Check)[$m]);
                }
            }
            $this->indiceLigne2Check = array_diff($this->indiceLigne2Check, $this->indiceWrongLigne);
            for ($m = 0; $m < count($this->indiceLigne2Check); $m++) {
                if (!in_array(array_keys($this->indiceLigne2Check)[$m], $this->indiceGoodLigne)) {
                    array_push($this->indiceGoodLigne, array_values($this->indiceLigne2Check)[$m]);
                }
            }
        }

        if ($siret && $tva) {
            for ($m = 0; $m < count($this->indiceLigne2Check); $m++) {
                $siretAPI = array_diff($listSiret, $wrongSirets) [array_values($this->indiceLigne2Check)[$m]];
                $sirenAPI = substr($siretAPI, 0, -5);
                $calculatedkey = $this->sirenToTVAKey($sirenAPI);
                $calculatedTVA = "FR" . $calculatedkey . $sirenAPI;
                $tvaCsv = ($csv->getContent())[array_values($this->indiceLigne2Check) [$m]]['tva_intra'];
                if (strcmp($tvaCsv, $calculatedTVA) != 0) {
                    array_push($this->indiceWrongLigne, array_values($this->indiceLigne2Check)[$m]);
                }
            }
            $this->indiceLigne2Check = array_diff($this->indiceLigne2Check, $this->indiceWrongLigne);
        }

        //*************VERIFICATION DE LA RAISON SOCIALE VIA UN ENVOIE GROUPE A L'API**************\\
        if ($raison_sociale) {
            for ($i = 0; $i < count($this->indiceLigne2Check); $i++) {
                $raison_csv = ($csv->getContent())[array_values($this->indiceLigne2Check)[$i]]['raison_sociale'];
                $API = null;
                $siretChecked = null;

                if ($SIRETORTVA) {
                    $API = $resultAPI;
                    $siretChecked = ($csv->getContent())[array_values($this->indiceLigne2Check)[$i]]['siret'];
                } else {
                    $API = $resultAPISiren;
                    $TVACSV = ($csv->getContent())[array_values($this->indiceLigne2Check)[$i]]['tva_intra'];
                    $siretChecked = substr($TVACSV, 4);//ce qui est en réalité le siren et non pas le siret
                }
                similar_text($API[$siretChecked]['raison_sociale'], $raison_csv, $percent);
                if ($percent < 50) {//TODO augmenter à 60 si on corrige
                    array_push($this->indiceWrongLigne, array_values($this->indiceLigne2Check)[$i]);
                }
            }
            $this->indiceLigne2Check = array_diff($this->indiceLigne2Check, $this->indiceWrongLigne);
        }

        $session->set('valid', $this->indiceGoodLigne);
        $session->set('invalid', $this->indiceWrongLigne);
        $session->set('erreur', $this->codeErreur);
        return new Response(json_encode(0));
    }

    /**
     * Cette fonction retourne la page de résultats.
     *
     * @Route("/results", name="results")
     */
    public function resultsAction(SessionInterface $session)
    {
        return $this->render('results.html.twig');
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

    /**
     * Cette fonction retourne le fichier CSV valide sous forme de téléchargement.
     *
     * @Route("/valid", name="valid")
     */
    public function validAction(SessionInterface $session)
    {
        $csv = unserialize($session->get('csv'));
        $valid = $session->get('valid');

        $file = $csv->getFinalCsv($valid, false);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="valide.csv"');
        $response->setCharset('utf-8');
        $response->setContent($file);
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    /**
     * Cette fonction retourne le fichier CSV invalide sous forme de téléchargement.
     *
     * @Route("/invalid", name="invalid")
     */
    public function invalidAction(SessionInterface $session)
    {
        $csv = unserialize($session->get('csv'));
        $invalid = $session->get('invalid');
        $erreurs = $session->get('erreur');

        $file = $csv->getFinalCsv($invalid, true, $erreurs);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="invalide.csv"');
        $response->setCharset('utf-8');
        $response->setContent($file);
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    public function sirenToTVAKey($siren)
    {
        $result = (12 + 3 * ($siren % 97)) % 97;
        if (strlen($result) == 1) {
            $result = "0" . $result;
        }
        return $result;
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

    public function verifSiret($csv, $i)
    {
        $siretCsv = ($csv->getContent())[$i]['siret'];
        if (strlen($siretCsv) == 14) {
            if (strpos($siretCsv, 'E') === false && strpos($siretCsv, '+') === false) {
                $this->indiceSiret2Check[$i] = $siretCsv;
            } else {
                if (!array_key_exists($i, $this->codeErreur)) {
                    $this->codeErreur[$i] = "Siret non conforme";
                } else {
                    $this->codeErreur[$i] = $this->codeErreur[$i] . ", Siret non conforme";
                }
                if ($this->trueLine) {
                    if (in_array($i, $this->indiceGoodLigne)) {
                        unset($this->indiceGoodLigne[$i]);
                    }
                    array_push($this->indiceWrongLigne, $i);
                }
                $this->trueLine = false;
            }
        } else {
            if (!array_key_exists($i, $this->codeErreur)) {
                $this->codeErreur[$i] = "Siret";
            } else {
                $this->codeErreur[$i] = $this->codeErreur[$i] . ", Siret";
            }
            if ($this->trueLine) {
                if (in_array($i, $this->indiceGoodLigne)) {
                    unset($this->indiceGoodLigne[$i]);
                }
                array_push($this->indiceWrongLigne, $i);
            }
            $this->trueLine = false;
        }
    }
}
