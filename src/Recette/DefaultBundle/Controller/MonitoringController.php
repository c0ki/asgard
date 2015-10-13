<?php

namespace Recette\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;

class MonitoringController extends Controller
{
    public function indexAction($type = null, $id = null)
    {
        /* @var $redmineHelper \Recette\DefaultBundle\Component\Helper\RedmineHelper */
        $redmineHelper = $this->container->get('redmine_helper');

        $conditions = array();
        switch ($type) {
            case 'priority':
                $conditions[] = "priority_id = {$id}";
                break;
            case 'category':
                $conditions[] = "category_id = {$id}";
                break;
            case 'status':
                $conditions[] = "status_id = {$id}";
                break;
        }

        // Lists issues
        $issues = $redmineHelper->listIssues($conditions);

        $indicators = array();
        $indicators['nb_issues'] = count($issues);
        $indicators['priorities'] = $redmineHelper->listPriorities();
        $indicators['categories'] = $redmineHelper->listCategories();
        $indicators['statuses'] = $redmineHelper->listStatuses();

        foreach ($issues as $issue) {
            if (!array_key_exists('nb_issues', $indicators['priorities'][$issue['priority_id']])) {
                $indicators['priorities'][$issue['priority_id']]['nb_issues'] = 0;
                $indicators['priorities'][$issue['priority_id']]['issues'] = array();
            }
            $indicators['priorities'][$issue['priority_id']]['nb_issues']++;
            $indicators['priorities'][$issue['priority_id']]['issues'][] = $issue;

            if (!array_key_exists('nb_issues', $indicators['categories'][$issue['category_id']])) {
                $indicators['categories'][$issue['category_id']]['nb_issues'] = 0;
                $indicators['categories'][$issue['category_id']]['issues'] = array();
            }
            $indicators['categories'][$issue['category_id']]['nb_issues']++;
            $indicators['categories'][$issue['category_id']]['issues'][] = $issue;

            if (!array_key_exists('nb_issues', $indicators['statuses'][$issue['status_id']])) {
                $indicators['statuses'][$issue['status_id']]['nb_issues'] = 0;
                $indicators['statuses'][$issue['status_id']]['issues'] = array();
            }
            $indicators['statuses'][$issue['status_id']]['nb_issues']++;
            $indicators['statuses'][$issue['status_id']]['issues'][] = $issue;
        }

        // Add

        return $this->render('RecetteDefaultBundle:Monitoring:index.html.twig', array('indicators' => $indicators, "type" => $type, "id" => $id));
    }
}
