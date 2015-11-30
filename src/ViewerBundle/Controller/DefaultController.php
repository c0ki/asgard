<?php

namespace ViewerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    public function indexAction(Request $request)
    {
        $viewer_helper = $this->container->get('viewer_helper');

        $form = $this->createForm('viewer_urls');
        $form->add('send', 'submit', array('label' => 'Envoyer'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $servers = $form->get('servers')->getData();
            $relativeUrl = $form->get('relativeurl')->getData();

            $urls = array();
            foreach ($servers as $serverId) {
                $urls[$serverId]['serverUrl'] = $viewer_helper->getServerUrl($serverId);
                $urls[$serverId]['url'] = $urls[$serverId]['serverUrl']->getUrl(). preg_replace('#/+#', '/', '/' . $relativeUrl);
            }

            $subform = $this->get('form.factory')->createNamedBuilder('changeviewer', 'form')
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


            return $this->render('ViewerBundle:Default:result.html.twig',
                                 array(
                                     'form' => $form->createView(),
                                     'subform' => $subform->createView(),
                                     'urls' => $urls,
                                 ));
        }

        return $this->render('ViewerBundle:Default:form.html.twig',
                             array(
                                 'form' => $form->createView(),
                             ));
    }


}
