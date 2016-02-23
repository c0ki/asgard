<?php

namespace Core\ProjectBundle\Controller;

use Core\ProjectBundle\Entity\Daemon;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminDaemonController extends Controller
{

    public function listAction()
    {
        $linkHelper = $this->container->get('link_helper');
        $projectHelper = $this->container->get('project_helper');

        $criteria = [];
        if ($projectHelper->hasProject()) {
            $criteria['project'] = $projectHelper->getProject();
        }
        if ($projectHelper->hasDomain()) {
            $criteria['domain'] = $projectHelper->getDomain();
        }

        $links = $linkHelper->findLinks($criteria, true);

        return $this->render('CoreProjectBundle:Admin:daemons.html.twig', array('links' => $links));
    }

    public function editAction(Request $request, $name)
    {
        $daemonHelper = $this->container->get('daemon_helper');
        $edit = true;
        $entity = $daemonHelper->getDaemonByName($name);
        if (!$entity) {
            $edit = false;
            $entity = new Daemon();
            $entity->setName('new');
            $entity->setDescription('Describe daemon here');
        }

        $form = $this->createForm('generic_entity',
            $entity,
            array('data_class' => 'Core\ProjectBundle\Entity\Daemon'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $OrmManager = $this->getDoctrine()->getManager();
            $entity = $form->getData();
            $OrmManager->persist($entity);
            $OrmManager->flush();

            if ($edit) {
                $this->addFlash('success', "Daemon '{$entity}' updated");
            } else {
                $this->addFlash('success', "Daemon '{$entity}' created");
            }

            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }

        return $this->render('CoreProjectBundle:Admin:daemon_edit.html.twig',
            array(
                'edit_mode' => $edit,
                'name' => $name,
                'form' => $form->createView(),
            ));
    }

    public function deleteAction(Request $request, $name)
    {
        $daemonHelper = $this->container->get('daemon_helper');
        $entity = $daemonHelper->getDaemonByName($name);
        if (empty($entity)) {
            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }
        $form = $this->createForm('generic_entity',
            $entity,
            array('data_class' => 'Core\ProjectBundle\Entity\Daemon', 'read_only' => true));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ormManager = $this->getDoctrine()->getManager();
            $ormManager->remove($entity);
            $ormManager->flush();
            $this->addFlash('success', "Daemon '{$entity}' deleted");

            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }

        return $this->render('CoreLayoutBundle:Default:confirm.html.twig',
            array(
                'form' => $form->createView(),
            ));
    }
}
