<?php

namespace Recette\DefaultBundle\Component\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;

class RedmineHelper
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container = null;

    protected $fields = array(
        'id' => "issues.id",
        'category_id' => "category_id",
        'category' => "issue_categories.name",
        'status_id' => "status_id",
        'status' => "issue_statuses.name",
        'assigned_to_id' => "issues.assigned_to_id",
        'assigned_to' => "CONCAT(users.firstname, ' ', users.lastname)",
        'priority_id' => "priority_id",
        'priority' => "enumerations.name",
        'id_almqc' => "c1.value",
        'id_almqc_court' => "CAST(SUBSTR(c1.value, 4) AS UNSIGNED)"
    );

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        foreach ($this->fields as $id => $field) {
            $this->fields[$id] .= " AS {$id}";
        }
    }

    /**
     * List priorities
     * @return array
     */
    public function listPriorities()
    {
        $query = <<<Query
    SELECT enumerations.id, enumerations.name
    FROM enumerations
    WHERE active = 1
        AND type = 'IssuePriority'
    ORDER BY enumerations.name DESC
Query;
        $priorities = array();
        $results = $this->container->get('doctrine.dbal.connection_factory')
                                   ->createConnection($this->container->getParameter('redmine_dbal'))
                                   ->fetchAll($query);
        foreach ($results as $result) {
            $priorities[$result['id']] = $result;
        }

        return $priorities;
    }

    /**
 * List categories
 * @return array
 */
    public function listCategories()
    {
        $query = <<<Query
    SELECT issue_categories.id, issue_categories.name
    FROM issue_categories
    WHERE project_id = 19
    ORDER BY issue_categories.name ASC
Query;
        $priorities = array();
        $results = $this->container->get('doctrine.dbal.connection_factory')
                                   ->createConnection($this->container->getParameter('redmine_dbal'))
                                   ->fetchAll($query);
        foreach ($results as $result) {
            $priorities[$result['id']] = $result;
        }

        return $priorities;
    }

    /**
     * List statuses
     * @return array
     */
    public function listStatuses()
    {
        $query = <<<Query
    SELECT issue_statuses.id, issue_statuses.name
    FROM issue_statuses
    ORDER BY issue_statuses.name ASC
Query;
        $priorities = array();
        $results = $this->container->get('doctrine.dbal.connection_factory')
                                   ->createConnection($this->container->getParameter('redmine_dbal'))
                                   ->fetchAll($query);
        foreach ($results as $result) {
            $priorities[$result['id']] = $result;
        }

        return $priorities;
    }




    /**
     * Get issue
     * @return array
     */
    public function getIssue($id)
    {
        $issue = null;
        $issues = $this->listIssues(array('issues.id = ' . $id));
        if (count($issues) > 0) {
            $issue = array_shift($issues);
        }
        return $issue;
    }

    /**
     * List issues
     * @return array
     */
    public function listIssues($conditions = null, $order = null)
    {
        $fields = implode(',', $this->fields);

        if (empty($conditions)) {
            $conditions = array();
        }
        $conditions[] = 'issues.project_id = 19';
        $conditions = implode(' AND ', $conditions);

        if (empty($order)) {
            $order = array('enumerations.name DESC', 'id_almqc_court ASC');
        }
        $order = implode(',', $order);

        $query = <<<Query
    SELECT {$fields}
    FROM issues
        INNER JOIN issue_categories ON issue_categories.id = issues.category_id
        INNER JOIN issue_statuses ON issue_statuses.id = issues.status_id
        INNER JOIN enumerations ON enumerations.id = issues.priority_id
        INNER JOIN users ON users.id = issues.assigned_to_id
        INNER JOIN custom_values c1 ON c1.customized_type = 'issue' AND c1.customized_id = issues.id AND c1.custom_field_id = 9
    WHERE {$conditions}
    ORDER BY {$order}
Query;
        $issues = array();
        $results = $this->container->get('doctrine.dbal.connection_factory')
                                   ->createConnection($this->container->getParameter('redmine_dbal'))
                                   ->fetchAll($query);
        foreach ($results as $result) {
            $issues[$result['id_almqc']] = $result;
        }

        return $issues;
    }

    /**
     * List issues by priority
     * @return array
     */
    public function listIssuesByPriority($id)
    {
        return $this->listIssues(array('issues.priority_id = ' . $id));
    }
}
