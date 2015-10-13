<?php

namespace DisplayBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    public function indexAction($project, Request $request)
    {
        //$project
        $form = $this->createForm('displayurl');
        $form->add('send', 'submit', array('label' => 'Envoyer'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $servers = $form->get('servers')->getData();
            $relativeUrl = $form->get('relativeurl')->getData();

            $urls = array();
            foreach ($servers as $server) {
                if (preg_match('#^(.*)/$#', $server, $matches)) {
                    $server = $matches[1];
                }
                $urls[] = $server . $relativeUrl;
            }
            $subform = $this->get('form.factory')->createNamedBuilder('changedisplay', 'form')
                            ->add('mode',
                                  'choice',
                                  array(
                                      'label' => "Mode d'affichage",
                                      'choices' => array(
                                          'mobile' => 'Mobile (<400px)',
                                          'tablet' => 'Tablette portrait (<620px)',
                                          'tabletlandscape' => 'Tablette paysage (<768px)',
                                          'desktop' => 'Ecran (>1000px)',
                                      ),
                                      'expanded' => true,
                                  ))
                            ->getForm();


            return $this->render('DisplayBundle:Default:result.html.twig',
                                 array(
                                     'form' => $form->createView(),
                                     'subform' => $subform->createView(),
                                     'urls' => $urls,
                                 ));
        }

        return $this->render('DisplayBundle:Default:form.html.twig',
                             array(
                                 'form' => $form->createView(),
                             ));
    }

}
