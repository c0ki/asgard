<?php

namespace Recette\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    const TemporaryPath = 'recette/import/';

    public function indexAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('inputfile', 'file', array(
                'label' => "Fichier à importer"))
            ->add('send', 'submit', array('label' => 'Envoyer'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            /* @var $uploadFile \Symfony\Component\HttpFoundation\File\UploadedFile */
            $uploadFile = $form['inputfile']->getData();

            // Check original extension
            if (!in_array($uploadFile->getClientOriginalExtension(), ['csv', 'xlsx'])) {
                $form->addError(new FormError('Invalid extension file'));
            }
            elseif ($uploadFile->getClientOriginalExtension() == 'xlsx' && $uploadFile->guessExtension() != 'zip') {
                $form->addError(new FormError('Invalid format to xlsx file'));
            }
            elseif ($uploadFile->getClientOriginalExtension() == 'csv' && $uploadFile->guessExtension() != 'csv') {
                $form->addError(new FormError('Invalid format to csv file'));
            }
            if ($form->isValid()) {
                $temporaryPath = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . self::TemporaryPath;
                $extension = $uploadFile->getClientOriginalExtension();
                /* @var $inputFile \Symfony\Component\HttpFoundation\File\File */
                $inputFile = $uploadFile->move($temporaryPath, rand(1, 99999).'.'.$extension);

                // Parse input file
                $data = array();
                if ($extension == 'xlsx') {
                    /* @var $parser \Recette\ImportBundle\Component\Xlsx\Parser */
                    $parser = $this->container->get('xlsx_parser');
                    $data = $parser->parse($inputFile, 1);
                }
                elseif ($extension == 'csv') {
                    /* @var $parser \Recette\ImportBundle\Component\Csv\Parser */
                    $parser = $this->container->get('csv_parser');
                    $data = $parser->parse($inputFile);
                }
                var_dump($data);
                exit();
            }

        }

        return $this->render('RecetteImportBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
        ));

        return $this->render('RecetteImportBundle:Default:index.html.twig', array('name' => $name));
    }
}
