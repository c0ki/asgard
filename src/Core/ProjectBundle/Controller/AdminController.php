<?php

namespace Core\ProjectBundle\Controller;

use Core\ProjectBundle\Entity\Project;
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

            $form = $this->createForm('generic_entity', $projectEntity, array('data_class' => 'Core\ProjectBundle\Entity\Project'));
            $form->add('send', 'submit', array('label' => 'Envoyer'));

            $form->handleRequest($request);

            if ($form->isValid()) {
                $OrmManager = $this->getDoctrine()->getManager();
                $projectEntity = $form->getData();

                $OrmManager->persist($projectEntity);
                $OrmManager->flush();

                if ($editProject) {
                    $this->container->get('alert_helper')->success('Modification OK');
                }
                else {
                    $this->container->get('alert_helper')->success('Creation OK');
                }

                return new RedirectResponse($this->generateUrl('core_project_admin'), 302);
            }

            return $this->render('CoreProjectBundle:Admin:edit.html.twig',
                                 array(
                                     'projects' => $projects,
                                     'edit_mode' => $editProject,
                                     'form' => $form->createView(),
                                 ));
        }

        return $this->render('CoreProjectBundle:Admin:list.html.twig',
                             array(
                                 'projects' => $projects,
                             ));
    }

}
