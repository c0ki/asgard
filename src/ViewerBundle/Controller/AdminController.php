<?php

namespace ViewerBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
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

        $serverUrls = $viewHelper->getServerUrls(false);
        $entity = array();
        $entity['serverUrls'] = $serverUrls;

        // Create an ArrayCollection of the current serverUrls objects in the database
        $originalServerUrls = new ArrayCollection();
        foreach ($serverUrls as $serverUrl) {
            $originalServerUrls->add($serverUrl);
        }

        $form = $this->createForm('server_urls', $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $OrmManager = $this->getDoctrine()->getManager();
            $data = $form->getData();
            $entities = $data['serverUrls'];

            // remove the relationship between the domain and the project
            foreach ($originalServerUrls as $serverUrl) {
                if (!in_array($serverUrl, $entities)) {
                    // if you wanted to delete the Tag entirely, you can also do that
                    $OrmManager->remove($serverUrl);
                }
            }

            foreach ($entities as $entity) {
                $OrmManager->persist($entity);
            }

            $OrmManager->flush();

            $this->container->get('alert_helper')->success('Update OK');

            return new RedirectResponse($this->generateUrl('viewer_admin_home'), 302);
        }

        return $this->render('ViewerBundle:Admin:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}
