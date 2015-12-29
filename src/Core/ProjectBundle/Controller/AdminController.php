<?php

namespace Core\ProjectBundle\Controller;

use Core\ProjectBundle\Entity\Domain;
use Core\ProjectBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{

    public function listAction() {
        return $this->render('CoreProjectBundle:Admin:list.html.twig');
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
                $this->container->get('alert_helper')->success("Project {$entity->getLabel()} updated");
            }
            else {
                $this->container->get('alert_helper')->success("Project {$entity->getLabel()} created");
            }

            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }

        return $this->render('CoreProjectBundle:Admin:project_edit.html.twig',
            array(
                'projects'  => $projects,
                'edit_mode' => $edit,
                'form'      => $form->createView(),
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
        $form->add('send', 'submit', array('label' => 'Submit'));
        $form->add('cancel', 'reset', array('label' => 'Cancel'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $OrmManager = $this->getDoctrine()->getManager();
            $entity = $form->getData();

            $OrmManager->persist($entity);
            $OrmManager->flush();

            if ($edit) {
                $this->container->get('alert_helper')->success("Domain {$entity->getLabel()} updated");
            }
            else {
                $this->container->get('alert_helper')->success("Domain {$entity->getLabel()} created");
            }

            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }

        return $this->render('CoreProjectBundle:Admin:domain_edit.html.twig',
            array(
                'domains'   => $domains,
                'edit_mode' => $edit,
                'form'      => $form->createView(),
            ));
    }

    public function deleteAction($confirm) {
        $projectHelper = $this->container->get('project_helper');
        if (!$confirm) {
            return $this->render('CoreProjectBundle:Admin:confirm.html.twig',
                array(
                    'element_type' => $projectHelper->hasProject() ? 'project' : 'domain',
                    'element'      => $projectHelper->hasProject() ? $projectHelper->getProject() : $projectHelper->getDomain(),
                ));
        }
        $ormManager = $this->getDoctrine()->getManager();

        if ($projectHelper->hasProject()) {
            $project = $projectHelper->getProject();
            $ormManager->remove($project);
            $ormManager->flush();
            $this->container->get('alert_helper')->success("Project {$project->getLabel()} deleted");

            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }
        elseif ($projectHelper->hasDomain()) {
            $domain = $projectHelper->getDomain();
            $ormManager->remove($domain);
            $ormManager->flush();
            $this->container->get('alert_helper')->success("Domain {$domain->getLabel()} deleted");

            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }

        return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
    }
}
