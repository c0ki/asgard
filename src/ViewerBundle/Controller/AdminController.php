<?php

namespace ViewerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{

    public function indexAction()
    {
        return $this->render('ViewerBundle:Default:list.html.twig');
    }

    public function editAction(Request $request)
    {
        $projectHelper = $this->container->get('project_helper');
        $viewHelper = $this->container->get('viewer_helper');

        $project = $projectHelper->getProject();
        $domain = $projectHelper->getDomain();

        $serverUrls = $viewHelper->getServerUrls($project, $domain);
        $entity = array();
        $entity['serverUrls'] = $serverUrls;

        $form = $this->createForm('server_urls', $entity);
        $form->add('send', 'submit', array('label' => 'Submit'));
        $form->add('cancel', 'reset', array('label' => 'Cancel'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $OrmManager = $this->getDoctrine()->getManager();
            $data = $form->getData();
            $entities = $data['serverUrls'];

            foreach ($entities as $entity) {
                $OrmManager->persist($entity);
            }
            $OrmManager->flush();

            // remove the relationship between the domain and the project
//            foreach ($originalDomains as $domain) {
//                if (!$entity->getDomains()->contains($domain)) {
//                    // if it was a many-to-one relationship, remove the relationship like this
//                    $domain->setProject(null);
//
//                    $OrmManager->persist($domain);
//                    // if you wanted to delete the Tag entirely, you can also do that
//                    $OrmManager->remove($domain);
//                }
//            }

            $this->container->get('alert_helper')->success('Creation OK');

            return new RedirectResponse($this->generateUrl('viewer_admin_home'), 302);
        }

        return $this->render('ViewerBundle:Admin:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}
