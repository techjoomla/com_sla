<?php
/**
 * @package    Sla
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Methods supporting a list of records.
 *
 * @since  1.0.0
 */
class SlaModelSlaActivities extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'sa.id',
				'sla_activity_type_id', 'sa.sla_activity_type_id',
				'sla_id', 'sa.sla_id',
				'sla_service_id', 'sa.sla_service_id',
				'cluster_id', 'sa.cluster_id',
				'license_id', 'sa.license_id',
				'status', 'todo.status',
				'ideal_time', 'todo.ideal_time',
				'ordering', 'sa.ordering',
				'state', 'sa.state',
				'created_by', 'sa.created_by',
				'users.id', 'todo.start_date',
				'start_date', 'todo.due_date',
				'due_date',
				'lead_consultant_id',
				'sla_status'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Ordering
	 * @param   string  $direction  Ordering dir
	 *
	 * @since    1.6
	 *
	 * @return  void
	 */
	protected function populateState($ordering = 'sa.id', $direction = 'desc')
	{
		$app = Factory::getApplication();

		$licenseId = $app->getUserStateFromRequest($this->context . '.filter.license_id', 'license_id');
		$this->setState('filter.license_id', $licenseId);

		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.0.0
	 */
	protected function getListQuery()
	{
		// Initialize variables.
		$app = Factory::getApplication();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$user = Factory::getUser();

		// Create the base select statement.
		$query->select(array('sa.*', 's.title as sla_title', 'todo.title as sla_service_title', 'users.name as uname'));
		$query->select(array('todo.status as todo_status', 'todo.ideal_time as todo_ideal_time'));
		$query->select(array('todo.start_date as todo_start_date', 'todo.due_date as todo_due_date'));
		$query->select(array('sat.title as activity_type_title'));
		$query->from($db->quoteName('#__tj_sla_activities', 'sa'));
		$query->join('INNER', $db->quoteName('#__tj_slas', 's') . ' ON (' . $db->quoteName('s.id') . ' = ' . $db->quoteName('sa.sla_id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_sla_activity_types', 'sat')
			. ' ON (' . $db->quoteName('sat.id') . ' = ' . $db->quoteName('sa.sla_activity_type_id') . ')');

		// Check com_cluster component is installed
		if (ComponentHelper::getComponent('com_cluster', true)->enabled)
		{
			$query->select('cl.name as school_name');
			$query->join('INNER', $db->quoteName('#__tj_clusters', 'cl')
			. ' ON (' . $db->quoteName('sa.cluster_id') . ' = ' . $db->quoteName('cl.id') . ')');
		}

		$query->join('INNER', $db->quoteName('#__jlike_todos', 'todo')
					. ' ON (' . $db->quoteName('todo.id') . ' = ' . $db->quoteName('sa.todo_id') . ')');
		$query->join('LEFT', $db->quoteName('#__users', 'users') . ' ON (' . $db->quoteName('todo.assigned_to') . ' = ' . $db->quoteName('users.id') . ')');

		// Check com_timelog component is installed
		if (ComponentHelper::getComponent('com_timelog', true)->enabled)
		{
			// If the sum of timelog is "20:50:00" the below query shows "20hr 50min" format
			$subQuery = $db->getQuery(true);
			$subQuery->select('TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(timelog))), "%Hhr %imin" )');
			$subQuery->from($db->quoteName('#__timelog_activities', 'tl'));
			$subQuery->where($db->quoteName('tl.client_id') . ' = ' . $db->qn('sa.id'));

			$query->select('(' . $subQuery . ') AS spentTime');
		}

		// Filter by dashboard_id
		$id = $this->getState('filter.id');

		if (!empty($id))
		{
			$query->where($db->quoteName('sa.id') . ' = ' . (int) $id);
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('sa.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(s.title LIKE ' . $search . ' OR todo.title LIKE ' . $search .
					' OR cl.name LIKE ' . $search . ' )');
			}
		}

		// Filter by Activity start
		$DateFilterFormat = JText::_('DATE_FORMAT_CALENDAR_DATE');
		$activityStartDate = $this->getState('filter.start_date');

		/*
		if (!empty($activityStartDate))
		{
			$query->where($db->quoteName('todo.start_date') . ' >= ' . $db->quote($activityStartDate));
		}
		*/

		// Filter by Activity due dates
		$activityDueDate   = $this->getState('filter.due_date');

		// Check activitites between dates
		if (!empty($activityStartDate) && !empty($activityDueDate))
		{
			$query->where(
							"DATE_FORMAT(todo.due_date, '$DateFilterFormat')" . ' BETWEEN ' .
							$db->quote($activityStartDate) . "AND " . $db->quote($activityDueDate)
						);
		}
		elseif (!empty($activityStartDate) && empty($activityDueDate))
		{
			$query->where("DATE_FORMAT(todo.due_date, '$DateFilterFormat')" . ' >= ' . $db->quote($activityStartDate));
		}
		elseif (empty($activityStartDate) && !empty($activityDueDate))
		{
			$query->where("DATE_FORMAT(todo.due_date, '$DateFilterFormat')" . ' <= ' . $db->quote($activityDueDate));
		}

		// Filter by sla_activity_type_id
		$slaActivityTypeId = $this->getState('filter.sla_activity_type_id');

		if (!empty($slaActivityTypeId))
		{
			$query->where($db->quoteName('sa.sla_activity_type_id') . ' = ' . (int) $slaActivityTypeId);
		}

		// Filter by leadconsultant
		$leadconsultantId = $this->getState('filter.lead_consultant_id');

		if (!empty($leadconsultantId))
		{
			$query->where($db->quoteName('users.id') . ' = ' . (int) $leadconsultantId);
		}

		// Filter by todos sla status
		$slaStatus = $this->getState('filter.sla_status');

		if (!empty($slaStatus))
		{
			$query->where($db->quoteName('todo.status') . ' = ' . $db->quote($slaStatus));
		}

		// Filter by created_by
		$created_by = $this->getState('filter.created_by');

		if (!empty($created_by))
		{
			$query->where($db->quoteName('sa.created_by') . ' = ' . (int) $created_by);
		}

		// Filter by state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('sa.state = ' . (int) $state);
		}
		elseif ($state === '')
		{
			$query->where('(sa.state = 0 OR sa.state = 1)');
		}

		// Filter by sla
		$sla = $this->getState('filter.sla_id');

		if (is_numeric($sla))
		{
			$query->where('sa.sla_id = ' . (int) $sla);
		}

		// Filter by sla service
		$slaService = $this->getState('filter.sla_service_id');

		if (is_numeric($slaService))
		{
			$query->where('sa.sla_service_id = ' . (int) $slaService);
		}

		// Filter by cluster
		$clusterId = $this->getState('filter.cluster_id');

		if (is_numeric($clusterId))
		{
			$query->where('sa.cluster_id = ' . (int) $clusterId);
		}

		// Check com_cluster component is installed
		if (ComponentHelper::getComponent('com_cluster', true)->enabled && !$user->authorise('core.manageall', 'com_cluster'))
		{
			JLoader::import("/components/com_cluster/includes/cluster", JPATH_ADMINISTRATOR);
			$clusterUserModel = ClusterFactory::model('ClusterUser', array('ignore_request' => true));
			$clusters = $clusterUserModel->getUsersClusters($user->id);

			foreach ($clusters as $cluster)
			{
				if (!empty($cluster->cluster_id))
				{
					// Todo: Need to introduce cluster level permission in com_sla
					if (RBACL::check($user->id, 'com_multiagency', 'core.adduser', $cluster->client_id))
					{
						$clusterIds[] = $cluster->cluster_id;
					}
				}
			}

			$query->where($db->qn('sa.cluster_id') . " IN ('" . implode("','", $clusterIds) . "')");

			$query->join('INNER', $db->quoteName('#__tj_cluster_nodes', 'cnode')
					. ' ON (' . $db->quoteName('cnode.cluster_id') . ' = ' . $db->quoteName('cl.id') . ')');
			$query->where('cnode.user_id = ' . (int) $user->id);
		}

		// Filter by cluster
		$licenseId = $this->getState('filter.license_id');

		if (is_numeric($licenseId))
		{
			$query->where('sa.license_id = ' . (int) $licenseId);
		}

		// Check permission to manage activities
		$canManageOwnActivity = $user->authorise('core.manage.activity.own', 'com_sla');
		$canManageAllActivity = $user->authorise('core.manage.activity', 'com_sla');

		if ($canManageOwnActivity && !$canManageAllActivity)
		{
			$query->where('todo.assigned_to = ' . (int) $user->id);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'sa.id');
		$orderDirn = $this->state->get('list.direction', 'DESC');

		if (!in_array(strtoupper($orderDirn), array('ASC', 'DESC', '')))
		{
			$orderDirn = 'DESC';
		}

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}
}
