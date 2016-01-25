<?php

namespace LogAnalyserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use LogAnalyserBundle\Component\File\File;

class DefaultController extends Controller
{

    public function formAction(Request $request)
    {
        $form = $this->createForm('logfile');
        $form->add('choice', 'submit', array('label' => 'Envoyer'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $identifier = $this->container->get('log_analyser.form.type.logfile')->saveLogfile(
                $form->get('server')->getData(),
                $form->get('log_file')->getData());
            return $this->redirectToRoute('log_analyser_analyse', array('identifier' => $identifier));
        }

        return $this->render('LogAnalyserBundle:Default:form.html.twig',
            array(
                'form' => $form->createView(),
            ));
    }

    public function listLogFilesAjaxAction(Request $request)
    {
//        if (!$request->isXmlHttpRequest()) {
//            throw new NotFoundHttpException();
//        }
        $server_id = null;
        if ($request->request->has('server')) {
            $server_id = $request->request->get('server');
        }
        elseif ($request->query->has('server')) {
            $server_id = $request->query->get('server');
        }
        else {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($this->container->get('log_analyser.form.type.logfile')->listLogFiles($server_id));
    }

    public function analyseAction($identifier, Request $request)
    {
        $dateStart = 0;
        $dateEnd = 0;
        /* @var $logfile \LogAnalyserBundle\Component\File\File */
        $logfile = $this->container->get('log_analyser.form.type.logfile')->getLogfile($identifier);

        /* @var $logFileHelper \LogAnalyserBundle\Component\Helper\LogFileHelper */
        $logFileHelper = $this->container->get('log_analyser_file_helper');

        $logType = $logFileHelper->getLogType($logfile);

        $logInfo = $logFileHelper->getLogInfo($logfile, $logType);

        $form = $this->createForm('analyse', null, array('logType' => $logType));

        $form->handleRequest($request);

        $results = null;
        if ($form->isValid()) {
            $results = $logFileHelper->analyseLog($logfile, $logType, $form->getData());
/*
                case 'errortype':
                    $results = `{$firstCommand} | cut -d']' -f4- | cut -d' ' -f2- | awk -F', referer' '{print $1}' | sort | uniq -c | sort -rg`;
                    $keys = ['nb', 'erreur'];
                    break;
*/
        }

        return $this->render('LogAnalyserBundle:Default:analyse.html.twig',
            array(
                'form' => $form->createView(),
                'identifier' => $identifier,
                'logInfo' => $logInfo,
                'results' => $results,
            ));
    }

    public function detailAction($identifier, $info) {
        /* @var $logfile \LogAnalyserBundle\Component\File\File */
        $logfile = $this->container->get('log_analyser.form.type.logfile')->getLogfile($identifier);

        $info = urldecode($info);

        $firstCommand = "cat {$logfile->getPathname()}";
        $grepInfo = addcslashes($info, '"\\');
        $grepInfo = str_replace('\\\\', '\\\\\\', $grepInfo);
        $results = `{$firstCommand} | grep "{$grepInfo}"`;
//        var_dump("{$firstCommand} | grep \"{$grepInfo}\"");

        $results = array_filter(explode("\n", $results));

        return $this->render('LogAnalyserBundle:Default:detail.html.twig',
                             array(
                                 'identifier' => $identifier,
                                 'info' => $info,
                                 'results' => $results,
                             ));
    }
}
