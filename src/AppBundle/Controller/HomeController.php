<?php

namespace AppBundle\Controller;

use AppBundle\Object\Csv;
use AppBundle\Object\FileUpload;
use AppBundle\Validation\CsvValidation;
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
            ->add('raisonSocialeChecked', CheckboxType::class, array(
                'label' => 'Raison Sociale',
                'required' => false
            ))
            ->add('adresseChecked', CheckboxType::class, array(
                'label' => 'Adresse',
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
            ->add('emailChecked', CheckboxType::class, array(
                'label' => 'Email',
                'required' => false
            ))
            ->add('telChecked', CheckboxType::class, array(
                'label' => 'Numéro de télephone',
                'required' => false
            ))
            ->add('profilUtilisateurChecked', CheckboxType::class, array(
                'label' => 'Profil utilisateur',
                'required' => false
            ))
            ->add('siretChecked', CheckboxType::class, array(
                'label' => 'Siret',
                'required' => false
            ))
            ->add('accordChecked', CheckboxType::class, array(
                'label' => 'Accord',
                'required' => false
            ))
            ->add('langueChecked', CheckboxType::class, array(
                'label' => 'Langue',
                'required' => false
            ))
            ->add('typeClientChecked', CheckboxType::class, array(
                'label' => 'Type client',
                'required' => false
            ))
            ->add('replaceTva', CheckboxType::class, array(
                'label' => 'remplacer Tva',
                'required' => false
            ))
            ->add('replaceCoordonnees', CheckboxType::class, array(
                'label' => 'Remplacer raison sociale, code postal, ville et adresse',
                'required' => false
            ))
            ->add('file', FileType::class)
            ->add('send', SubmitType::class, array(
                'label' => 'Envoyer'
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // On charge le fichier CSV dans la classe Csv
            $csv = new Csv();
            if (!$csv->verif_header($fileUpload->getFile()->getPathname())) {
                return $this->render('home.html.twig', array(
                    'form' => $form->createView(),
                    'alert' => 1
                ));
            }

            $csv->lire_csv($fileUpload->getFile()->getPathname());

            // On stocke le CSV et les tests à effectuer dans la session
            $session->set('csv', serialize($csv));
            $tests = array(
                'tva' => $fileUpload->isTvaChecked(),
                'raisonSociale' => $fileUpload->isRaisonSocialeChecked(),
                'adresse' => $fileUpload->isAdresseChecked(),
                'codePostal' => $fileUpload->isCodePostalChecked(),
                'ville' => $fileUpload->isVilleChecked(),
                'email' => $fileUpload->isEmailChecked(),
                'tel' => $fileUpload->isTelChecked(),
                'profilUtilisateur' => $fileUpload->isProfilUtilisateurChecked(),
                'siret' => $fileUpload->isSiretChecked(),
                'accord' => $fileUpload->isAccordChecked(),
                'langue' => $fileUpload->isLangueChecked(),
                'typeClient' => $fileUpload->isTypeClientChecked(),
                'replaceTva' => $fileUpload->isReplaceTva(),
                'replaceCoordonnees' => $fileUpload->isReplaceCoordonnees()
            );
            $session->set('tests', serialize($tests));

            // On retourne la page contenant la barre de progression
            return $this->render('running.html.twig');
        }

        // On retourne le formulaire
        return $this->render('home.html.twig', array(
            'form' => $form->createView(),
            'alert' => 0
        ));
    }

    /**
     * Cette fonction contient le script de vérification du fichier CSV.
     *
     * @Route("/running", name="running")
     */
    public function runningAction(SessionInterface $session, CsvValidation $csvValidation)
    {
        $csv = unserialize($session->get('csv'));
        $tests = unserialize($session->get('tests'));

        $start = microtime(TRUE);
        $result = $csvValidation->checkCsv($csv->getContent(), $tests);
        $stop = microtime(TRUE);

        $executionTime = $stop - $start;

        $session->set('valid', serialize($result['valid']));
        $session->set('invalid', serialize($result['invalid']));
        $session->set('time', $executionTime);

        return new Response(json_encode(0));
    }

    /**
     * Cette fonction retourne la page de résultats.
     *
     * @Route("/results", name="results")
     */
    public function resultsAction(SessionInterface $session)
    {
        $tests = unserialize($session->get('tests'));
        $invalid = unserialize($session->get('invalid'));
        $time = $session->get('time');

        $nameTests = array_keys($tests, 1);
        for ($i = 0; $i < count($nameTests); $i++) {
            switch ($nameTests[$i]) {
                case "raisonSociale":
                    $nameTests[$i] = "Raison Sociale";
                    break;
                case "codePostal":
                    $nameTests[$i] = "Code postal";
                    break;
                case "tel":
                    $nameTests[$i] = "Numéro de téléphone";
                    break;
                case "profilUtilisateur":
                    $nameTests[$i] = "Profil utilisateur";
                    break;
                case "typeClient":
                    $nameTests[$i] = "Type client";
                    break;
                case "replaceTva":
                    $nameTests[$i] = "Remplacement de la TVA";
                    break;
                case "replaceCoordonnees":
                    $nameTests[$i] = "Remplacement de la raison sociale, du code postal, de la ville et de l'adresse";
                    break;
            }
        }

        return $this->render('results.html.twig', array(
            'lignesInvalides' => count($invalid),
            'tests' => $nameTests,
            'time' => round($time, 2)
        ));
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
            $progress = $session->get('progress');
        } else {
            $progress = 1;
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
        $valid = unserialize($session->get('valid'));

        $file = $csv->array2Csv($valid, true);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="valide.csv"');
        $response->setCharset('ISO-8859-1');
        $response->setContent(utf8_decode($file));
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
        $invalid = unserialize($session->get('invalid'));

        $file = $csv->array2Csv($invalid, false);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="invalide.csv"');
        $response->setCharset('ISO-8859-1');
        $response->setContent(utf8_decode($file));
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }
}
