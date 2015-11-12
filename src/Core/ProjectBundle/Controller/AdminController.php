<?php

namespace Core\ProjectBundle\Controller;

use Core\ProjectBundle\Entity\Environment;
use Core\ProjectBundle\Entity\Project;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{

    public function listAction()
    {
        $projectHelper = $this->container->get('project_helper');

        $projects = $projectHelper->listProjects();
        $environments = $projectHelper->listEnvironments();

        return $this->render('CoreProjectBundle:Admin:list.html.twig',
                             array(
                                 'projects' => $projects,
                                 'environments' => $environments,
                             ));
    }

    public function projectAction(Request $request, $project)
    {
        $projectHelper = $this->container->get('project_helper');

        $projects = $projectHelper->listProjects();

        $edit = false;
        if ($projectHelper->hasProjectByName($project)) {
            $edit = true;
            $entity = $projectHelper->getProjectByName($project);
        }
        else {
            $entity = new Project();
            $entity->setName($project);
            $entity->setDescription('Describe project here');
        }

        // Create an ArrayCollection of the current Environment objects in the database
//        $originalEnvironments = new ArrayCollection();
//        foreach ($projectEntity->getEnvironments() as $environment) {
//            $originalEnvironments->add($environment);
//        }

        $form = $this->createForm('generic_entity',
                                  $entity,
                                  array('data_class' => 'Core\ProjectBundle\Entity\Project'));
        $form->add('send', 'submit', array('label' => 'Submit'));
        $form->add('cancel', 'reset', array('label' => 'Cancel'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $OrmManager = $this->getDoctrine()->getManager();
            $entity = $form->getData();

            // remove the relationship between the environment and the project
//            foreach ($originalEnvironments as $environment) {
//                if (!$entity->getEnvironments()->contains($environment)) {
//                    // if it was a many-to-one relationship, remove the relationship like this
//                    $environment->setProject(null);
//
//                    $OrmManager->persist($environment);
//                    // if you wanted to delete the Tag entirely, you can also do that
//                    $OrmManager->remove($environment);
//                }
//            }

            $OrmManager->persist($entity);
            $OrmManager->flush();

            if ($edit) {
                $this->container->get('alert_helper')->success('Modification OK');
            }
            else {
                $this->container->get('alert_helper')->success('Creation OK');
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

    public function environmentAction(Request $request, $environment)
    {
        $projectHelper = $this->container->get('project_helper');

        $environments = $projectHelper->listEnvironments();

        $edit = false;
        if ($projectHelper->hasEnvironmentByName($environment)) {
            $edit = true;
            $entity = $projectHelper->getEnvironmentByName($environment);
        }
        else {
            $entity = new Environment();
            $entity->setName($environment);
            $entity->setDescription('Describe environment here');
        }

        $form = $this->createForm('generic_entity',
                                  $entity,
                                  array('data_class' => 'Core\ProjectBundle\Entity\Environment'));
        $form->add('send', 'submit', array('label' => 'Submit'));
        $form->add('cancel', 'reset', array('label' => 'Cancel'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $OrmManager = $this->getDoctrine()->getManager();
            $entity = $form->getData();

            $OrmManager->persist($entity);
            $OrmManager->flush();

            if ($edit) {
                $this->container->get('alert_helper')->success('Modification OK');
            }
            else {
                $this->container->get('alert_helper')->success('Creation OK');
            }

            return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
        }

        return $this->render('CoreProjectBundle:Admin:environment_edit.html.twig',
                             array(
                                 'environments' => $environments,
                                 'edit_mode' => $edit,
                                 'form' => $form->createView(),
                             ));
    }

}
