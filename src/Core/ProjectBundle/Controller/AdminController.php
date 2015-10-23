<?php

namespace Core\ProjectBundle\Controller;

use Core\ProjectBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    public function indexAction(Request $request, $project = null)
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

            $form = $this->createForm('entity', $projectEntity, array('entity' => 'Core\ProjectBundle\Entity\Project'));
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

            return $this->render('CoreProjectBundle:Admin:edit.html.twig',
                                 array(
                                     'projects' => $projects,
                                     'edit_mode' => $editProject,
                                     'form' => $form->createView(),
                                 ));
        }

        return $this->render('CoreProjectBundle:Admin:index.html.twig',
                             array(
                                 'projects' => $projects,
                             ));
    }

}
