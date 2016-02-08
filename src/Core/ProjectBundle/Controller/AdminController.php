<?php

namespace Core\ProjectBundle\Controller;

use Core\ProjectBundle\Entity\Daemon;
use Core\ProjectBundle\Entity\Domain;
use Core\ProjectBundle\Entity\Link;
use Core\ProjectBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{

    public function listAction() {
        $links = $this->container->get('link_helper')->listLinks();
        return $this->render('CoreProjectBundle:Admin:list.html.twig', array('links' => $links));
    }

    public function projectAction(Request $request) {
        $projectHelper = $this->container->get('project_helper');

        $projects = $projectHelper->listProjects();

        $edit = true;
        $entity = $projectHelper->getProject();
        if (!$entity) {
            $edit = false;
            $entity = new Project();
            $entity->setName('new');
            $entity->setDescription('Describe project here');
        }

        // Create an ArrayCollection of the current Domain objects in the database
//        $originalDomains = new ArrayCollection();
//        foreach ($projectEntity->getDomains() as $domain) {
//            $originalDomains->add($domain);
//        }

        $form = $this->createForm('generic_entity',
                                  $entity,
                                  array('data_class' => 'Core\ProjectBundle\Entity\Project'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $OrmManager = $this->getDoctrine()->getManager();
            $entity = $form->getData();

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

            $OrmManager->persist($entity);
            $OrmManager->flush();

            if ($edit) {
                $this->container->get('alert_helper')->success("Project '{$entity->getLabel()}' updated");
            }
            else {
                $this->container->get('alert_helper')->success("Project '{$entity->getLabel()}' created");
            }

            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }

        return $this->render('CoreProjectBundle:Admin:project_edit.html.twig',
                             array(
                                 'projects' => $projects,
                                 'edit_mode' => $edit,
                                 'form' => $form->createView(),
                             ));
    }

    public function domainAction(Request $request) {
        $projectHelper = $this->container->get('project_helper');

        $domains = $projectHelper->listDomains();

        $edit = true;
        $entity = $projectHelper->getDomain();
        if (!$entity) {
            $edit = false;
            $entity = new Domain();
            $entity->setName('new');
            $entity->setDescription('Describe domain here');
        }

        $form = $this->createForm('generic_entity',
                                  $entity,
                                  array('data_class' => 'Core\ProjectBundle\Entity\Domain'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $OrmManager = $this->getDoctrine()->getManager();
            $entity = $form->getData();

            $OrmManager->persist($entity);
            $OrmManager->flush();

            if ($edit) {
                $this->container->get('alert_helper')->success("Domain '{$entity->getLabel()}' updated");
            }
            else {
                $this->container->get('alert_helper')->success("Domain '{$entity->getLabel()}' created");
            }

            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }

        return $this->render('CoreProjectBundle:Admin:domain_edit.html.twig',
                             array(
                                 'domains' => $domains,
                                 'edit_mode' => $edit,
                                 'form' => $form->createView(),
                             ));
    }

    public function deleteAction(Request $request) {
        $projectHelper = $this->container->get('project_helper');
        if ($projectHelper->hasProject()) {
            $entity = $projectHelper->getProject();
            $form = $this->createForm('generic_entity',
                                      $entity,
                                      array('data_class' => 'Core\ProjectBundle\Entity\Project', 'read_only' => true));
        }
        elseif ($projectHelper->hasDomain()) {
            $entity = $projectHelper->getDomain();
            $form = $this->createForm('generic_entity',
                                      $entity,
                                      array('data_class' => 'Core\ProjectBundle\Entity\Domain', 'read_only' => true));
        }
        else {
            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ormManager = $this->getDoctrine()->getManager();

            if ($projectHelper->hasProject()) {
                $project = $projectHelper->getProject();
                $ormManager->remove($project);
                $ormManager->flush();
                $this->container->get('alert_helper')->success("Project '{$project->getLabel()}' deleted");

                return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
            }
            elseif ($projectHelper->hasDomain()) {
                $domain = $projectHelper->getDomain();
                $ormManager->remove($domain);
                $ormManager->flush();
                $this->container->get('alert_helper')->success("Domain '{$domain->getLabel()}' deleted");

                return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
            }

            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }

        return $this->render('CoreLayoutBundle:Default:confirm.html.twig',
                             array(
                                 'form' => $form->createView(),
                             ));
    }

    public function daemonAction(Request $request, $name) {
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
                $this->container->get('alert_helper')->success("Daemon '{$entity->getLabel()}' updated");
            }
            else {
                $this->container->get('alert_helper')->success("Daemon '{$entity->getLabel()}' created");
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

    public function daemonDeleteAction(Request $request, $name) {
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
            $this->container->get('alert_helper')->success("Daemon '{$entity->getLabel()}' deleted");

            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }

        return $this->render('CoreLayoutBundle:Default:confirm.html.twig',
                             array(
                                 'form' => $form->createView(),
                             ));
    }

    public function linkAction(Request $request, $id) {
        $linkHelper = $this->container->get('link_helper');
        $edit = true;
        $entity = $linkHelper->getLinkById($id);
        if (!$entity) {
            $edit = false;
            $entity = new Link();
        }

        $form = $this->createForm('generic_entity',
                                  $entity,
                                  array('data_class' => 'Core\ProjectBundle\Entity\Link'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $OrmManager = $this->getDoctrine()->getManager();
            $entity = $form->getData();
            $OrmManager->persist($entity);
            $OrmManager->flush();

            if ($edit) {
                $this->container->get('alert_helper')->success("Link '{$entity->getProject()->getLabel()} / {$entity->getDomain()->getLabel()} / {$entity->getDaemon()->getLabel()} [{$entity->getServer()}]' updated");
            }
            else {
                $this->container->get('alert_helper')->success("Link '{$entity->getProject()->getLabel()} / {$entity->getDomain()->getLabel()} / {$entity->getDaemon()->getLabel()} [{$entity->getServer()}]' created");
            }

            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }

        $links = $this->container->get('link_helper')->listLinks();
        return $this->render('CoreProjectBundle:Admin:link_edit.html.twig',
                             array(
                                 'links' => $links,
                                 'edit_mode' => $edit,
                                 'id' => $id,
                                 'form' => $form->createView(),
                             ));
    }

    public function linkDeleteAction(Request $request, $id) {
        $linkHelper = $this->container->get('link_helper');
        $entity = $linkHelper->getLinkById($id);
        if (empty($entity)) {
            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }
        $form = $this->createForm('generic_entity',
                                  $entity,
                                  array('data_class' => 'Core\ProjectBundle\Entity\Link', 'read_only' => true));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $linkLabel = "{$entity->getProject()} / {$entity->getDomain()} / {$entity->getDaemon()} [{$entity->getServer()}]";
            $ormManager = $this->getDoctrine()->getManager();
            $ormManager->remove($entity);
            $ormManager->flush();
            $this->container->get('alert_helper')->success("Link '{$linkLabel}' deleted");

            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }

        return $this->render('CoreLayoutBundle:Default:confirm.html.twig',
                             array(
                                 'form' => $form->createView(),
                             ));
    }
}
