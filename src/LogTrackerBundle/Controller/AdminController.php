<?php

namespace LogTrackerBundle\Controller;

use LogTrackerBundle\Entity\LogFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    public function indexAction() {
        $projectHelper = $this->container->get('project_helper');
        $logfileHelper = $this->container->get('logfile_helper');

        $criteria = array(
            'project' => $projectHelper->getProject(),
            'domain'  => $projectHelper->getDomain(),
        );
        $logfiles = array();
        $logs = $logfileHelper->listLogs($criteria);
        foreach ($logs as $logfile) {
            $logfiles[(string)$logfile->getLink()->getProject()][(string)$logfile->getLink()->getDomain()][] = $logfile;
        }
        if ($projectHelper->hasProject() && array_key_exists((string)$projectHelper->getProject(), $logfiles)) {
            $logfiles = $logfiles[(string)$projectHelper->getProject()];
            if ($projectHelper->hasDomain() && array_key_exists((string)$projectHelper->getDomain(), $logfiles)) {
                $logfiles = $logfiles[(string)$projectHelper->getDomain()];
            }
        }
        elseif ($projectHelper->hasDomain()) {
            foreach ($logfiles as &$logs) {
                if (array_key_exists((string)$projectHelper->getDomain(), $logs)) {
                    $logs = $logs[(string)$projectHelper->getDomain()];
                }
                else {
                    $logs = array();
                }
            }
        }

        return $this->render('LogTrackerBundle:Admin:index.html.twig', array('logfiles' => $logfiles));
    }

    public function logfileEditAction(Request $request, $id) {
        $logfileHelper = $this->container->get('logfile_helper');
        $edit = true;
        $entity = $logfileHelper->getLogById($id);
        if (!$entity) {
            $edit = false;
            $entity = new LogFile();
        }

        $form = $this->createForm('generic_entity',
            $entity,
            array('data_class' => 'LogTrackerBundle\Entity\LogFile'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $ormManager = $this->getDoctrine()->getManager();
            $entity = $form->getData();
            $ormManager->persist($entity);
            $ormManager->flush();

            if ($edit) {
                $this->addFlash('success', "Logfile '{$entity}' updated");
            }
            else {
                $this->addFlash('success', "Logfile '{$entity}' created");
            }

            return new RedirectResponse($this->generateUrl('log_tracker_admin'), 302);
        }

        return $this->render('LogTrackerBundle:Admin:logfile_edit.html.twig',
            array(
                'edit_mode' => $edit,
                'form'      => $form->createView(),
            ));
    }

    public function logfileDeleteAction(Request $request, $id) {
        $logfileHelper = $this->container->get('logfile_helper');
        $entity = $logfileHelper->getLogById($id);
        if (empty($entity)) {
            return new RedirectResponse($this->generateUrl('log_tracker_admin'), 302);
        }
        $entityLabel = (string)$entity;
        $form = $this->createForm('generic_entity',
            $entity,
            array('data_class' => 'LogTrackerBundle\Entity\LogFile', 'read_only' => true));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ormManager = $this->getDoctrine()->getManager();
            $ormManager->remove($entity);
            $ormManager->flush();
            $this->addFlash('success', "Logfile '{$entityLabel}' deleted");

            return new RedirectResponse($this->generateUrl('log_tracker_admin'), 302);
        }

        return $this->render('CoreLayoutBundle:Default:confirm.html.twig',
            array(
                'form' => $form->createView(),
            ));
    }
}
