<?php

namespace Core\ProjectBundle\Controller;

use Core\ProjectBundle\Entity\Project;
use Core\ProjectBundle\Entity\Environment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{

    public function projectsAction(Request $request, $project = null)
    {
        $projectHelper = $this->container->get('project_helper');

        $projects = $projectHelper->all();

        if (!is_null($project)) {
            $editProject = false;
            if ($projectHelper->hasByName($project)) {
                $editProject = true;
                $projectEntity = $projectHelper->getByName($project);
            }
            else {
                $projectEntity = new Project();
                $projectEntity->setName($project);
                $projectEntity->setDescription('Describe project here');
            }

            $form = $this->createForm('generic_entity', $projectEntity, array('entity' => 'Core\ProjectBundle\Entity\Project'));
            $form->add('send', 'submit', array('label' => 'Envoyer'));

            $form->handleRequest($request);

            if ($form->isValid()) {
                $OrmManager = $this->getDoctrine()->getManager();
                $projectEntity = $form->getData();

                $OrmManager->persist($projectEntity);
                $OrmManager->flush();

                $msg = 'Create OK';
                if ($editProject) {
                    $msg = 'Edit OK';
                }

                return new RedirectResponse($this->generateUrl('core_project_admin'), 302, array('x-msg' => $editProject));
            }

            return $this->render('CoreProjectBundle:Admin/Project:edit.html.twig',
                                 array(
                                     'projects' => $projects,
                                     'edit_mode' => $editProject,
                                     'form' => $form->createView(),
                                 ));
        }

        return $this->render('CoreProjectBundle:Admin/Project:index.html.twig',
                             array(
                                 'projects' => $projects,
                             ));
    }

    public function environmentsAction(Request $request, $project, $environment = null)
    {
        $projectHelper = $this->container->get('project_helper');
        if (!$projectHelper->hasByName($project)) {
            // Redirection sur la liste des projets
        }
        $project = $projectHelper->getByName($project);

        $environments = $project->getEnvironments();

        if (!is_null($environment)) {
            $environmentHelper = $this->container->get('environement_helper');
            $edit = false;
            if ($environmentHelper->hasByName($environment)) {
                $edit = true;
                $environmentEntity = $environmentHelper->getByName($environment);
            }
            else {
                $environmentEntity = new Environment();
                $environmentEntity->setName($environment);
                $environmentEntity->setDescription('Describe project here');
            }

            $form = $this->createForm('generic_entity', $environmentEntity, array('entity' => 'Core\ProjectBundle\Entity\Environment'));
            $form->add('send', 'submit', array('label' => 'Envoyer'));

            $form->handleRequest($request);

            if ($form->isValid()) {
                $OrmManager = $this->getDoctrine()->getManager();
                $environmentEntity = $form->getData();

                $OrmManager->persist($environmentEntity);
                $OrmManager->flush();

                $msg = 'Create OK';
                if ($edit) {
                    $msg = 'Edit OK';
                }

                return new RedirectResponse($this->generateUrl('core_project_admin'), 302, array('x-msg' => $edit));
            }

            return $this->render('CoreProjectBundle:Admin/Environment:edit.html.twig',
                                 array(
                                     'environments' => $environments,
                                     'edit_mode' => $edit,
                                     'form' => $form->createView(),
                                 ));
        }

        return $this->render('CoreProjectBundle:Admin/Environment:index.html.twig',
                             array(
                                 'environments' => $environments,
                             ));
    }

}
