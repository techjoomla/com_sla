<?php
/**
 * @package    Sla
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.

defined('_JEXEC') or die;

jimport('techjoomla.tjnotifications.tjnotifications');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

JLoader::import('components.com_sla.includes.sla', JPATH_ADMINISTRATOR);
/**
 * Item Model for an Sla activity.
 *
 * @since  1.0.0
 */
class SlaModelSlaActivity extends AdminModel
{
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm|boolean  A JForm object on success, false on failure
	 *
	 * @since   1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_sla.slaactivity', 'slaactivity', array('control' => 'jform', 'load_data' => $loadData));

		return empty($form) ? false : $form;
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable    A database object
	 */
	public function getTable($type = 'SlaActivities', $prefix = 'SlaTable', $config = array())
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_sla/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return   void
	 *
	 * @since    1.0.0
	 */

	protected function populateState()
	{
		$jinput = Factory::getApplication()->input;
		$id = ($jinput->get('id'))?$jinput->get('id'):$jinput->get('id');
		$this->setState('slaactivity.id', $id);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	$data  The data for the form.
	 *
	 * @since	1.0.0
	 */
	protected function loadFormData()
	{
		$data = $this->getItem();

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  \JObject|boolean  Object on success, false on failure.
	 *
	 * @since   1.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if (!empty($item->id))
			{
				JLoader::import('components.com_jlike.tables.todos', JPATH_ADMINISTRATOR);
				$todoTable = JTable::getInstance('Todos', 'JlikeTable');
				$todoTable->load(array('parent_id' => $item->todo_id));

				$item->cluster_user = $todoTable->assigned_to;
				$item->due_date = $todoTable->due_date;
			}
		}

		return $item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function save($data)
	{
		$pk = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('slaactivity.id');

		$isNew = $pk ? false : true;

		// Get null data time
		$db = Factory::getDbo();
		$nullDate = $db->getNullDate();

		// Get SLA details
		$slaClusterXrefTable = SlaFactory::table("slaclusterxrefs");
		$slaClusterXrefTable->load(array('license_id' => $data['license_id']));

		$todoData                = array();
		$todoData['id']          = $data['todo_id'];
		$todoData['assigned_to'] = $data['lead_consultant_id'];
		$todoData['start_date']  = (!empty($data['start_date'])) ? $data['start_date'] : $nullDate;
		$todoData['due_date']    = (!empty($data['due_date'])) ? $data['due_date'] : $nullDate;
		$todoData['title']       = $data['activity_name'];
		$todoData['sender_msg']  = $data['activity_desc'];
		$todoData['ideal_time']  = $data['ideal_time'];

		$slaServiceObj = SlaSlaService::getInstance($service->id);
		$jlikeTodoId   = $slaServiceObj->saveTodo($todoData);

		// Save SLA activities
		$slaSlaActivity                       = SlaSlaActivity::getInstance($pk);
		$slaSlaActivity->id                   = $pk;
		$slaSlaActivity->sla_activity_type_id = $data['sla_activity_type_id'];
		$slaSlaActivity->sla_id               = $slaClusterXrefTable->sla_id;
		$slaSlaActivity->cluster_id           = $slaClusterXrefTable->cluster_id;
		$slaSlaActivity->license_id           = $data['license_id'];
		$slaSlaActivity->todo_id              = $jlikeTodoId;

		// Store the data.
		if ($slaSlaActivity->save())
		{
			$this->setState('slaactivity.id', $slaActivity->id);

			// Add/Update record in #_jlike_content #_jlike_todos only when user set assigned_to
			$slaSlaActivity->slaActivityData = $data;
			$dispatcher   = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('system');
			$dispatcher->trigger('onAfterSlaActivitySave', array($slaSlaActivity, $isNew));

			return $slaSlaActivity;
		}
		else
		{
			$this->setError($slaSlaActivity->getError());

			return false;
		}
	}

	/**
	 * Method to get category name
	 *
	 * @param   int  $id  pk
	 *
	 * @return  boolean  true/false or int $id
	 *
	 * @since  1.0
	 */
	public function delete($id)
	{
		$id = (!empty($id)) ? $id : (int) $this->getState('slaactivity.id');

		if (JFactory::getUser()->authorise('core.delete', 'com_sla') !== true)
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$table = $this->getTable();

		if ($table->delete($id) === true)
		{
			return $id;
		}
		else
		{
			return false;
		}

		return true;
	}
}
