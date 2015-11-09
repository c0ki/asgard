<?php

namespace Core\ProjectBundle\Controller;

use Core\ProjectBundle\Entity\Project;
use Doctrine\Common\Collections\ArrayCollection;
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

            // Create an ArrayCollection of the current Environment objects in the database
            $originalEnvironments = new ArrayCollection();
            foreach ($projectEntity->getEnvironments() as $environment) {
                $originalEnvironments->add($environment);
            }

            $form = $this->createForm('generic_entity', $projectEntity, array('data_class' => 'Core\ProjectBundle\Entity\Project'));
            $form->add('send', 'submit', array('label' => 'Envoyer'));

            $form->handleRequest($request);

            if ($form->isValid()) {
                $OrmManager = $this->getDoctrine()->getManager();
                $projectEntity = $form->getData();

                // remove the relationship between the environment and the project
                foreach ($originalEnvironments as $environment) {
                    if (!$projectEntity->getEnvironments()->contains($environment)) {
                        // if it was a many-to-one relationship, remove the relationship like this
                        $environment->setProject(null);

                        $OrmManager->persist($environment);
                        // if you wanted to delete the Tag entirely, you can also do that
                        $OrmManager->remove($environment);
                    }
                }

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
