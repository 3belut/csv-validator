<?php

namespace AppBundle\Controller;

use AppBundle\Object\Csv;
use AppBundle\Object\FileUpload;
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
            ->add('pieceJointeChecked', CheckboxType::class, array(
                'label' => 'Pièce jointe',
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
            return new Response(json_encode($session->get('progress')));
        } else
            return new Response(json_encode(0));
    }
}
