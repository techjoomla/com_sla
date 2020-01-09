<?php
/**
 * @package    Sla
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
JLoader::import('components.com_sla.includes.sla', JPATH_ADMINISTRATOR);
JLoader::import('components.com_subusers.includes.rbacl', JPATH_ADMINISTRATOR);

/**
 * The sla activity controller
 *
 * @since  1.0.0
 */
class SlaControllerSlaActivity extends FormController
{
	/**
	 * Function to update Jlike todos
	 *
	 * @return  object  object
	 */
	public function updateTodo()
	{
		if (!JSession::checkToken('get'))
		{
			echo new JResponseJson(null, Text::_('JINVALID_TOKEN'), true);
		}
		else
		{
			$app = Factory::getApplication();
			$input = $app->input;

			$user            = Factory::getUser();
			$currentDateTime = Factory::getDate()->toSql();

			$todoId = $input->get('todoId', 0, 'INT');
			$todoStatus = $input->get('todoStatus', '', 'WORD');

			if (empty($todoId) || empty($todoStatus))
			{
				echo new JResponseJson(null, Text::_('JERROR_ALERTNOAUTHOR'), true);

				return;
			}

			JLoader::import('components.com_jlike.models.recommendationform', JPATH_SITE);
			$recommendationFormModel = JModelLegacy::getInstance('RecommendationForm', 'JlikeModel', array('ignore_request' => true));

			$todoData['id']            = $todoId;
			$todoData['status']        = $todoStatus;
			$todoData['modified_date'] = $currentDateTime;
			$todoData['done_date']     = $currentDateTime;
			$todoData['done_by']       = $user->id;
			$todoData['plg_type']      = 'system';
			$todoData['plg_name']      = 'plg_System_Dpe';

			if ($todoStatus == 'I')
			{
				$todoData['done_by'] = 0;
			}

			if (!$recommendationFormModel->save($todoData))
			{
				echo new JResponseJson(null, Text::_('JERROR_ALERTNOAUTHOR'), true);

				return;
			}

			echo new JResponseJson(null, Text::_('COM_SLA_TODO_STATUS_UPDATED'), false);

			return;
		}
	}

	/**
	 * Function to delete SLA Activity
	 *
	 * @return  object  object
	 */
	public function deleteActivity()
	{
		if (!JSession::checkToken('get'))
		{
			echo new JResponseJson(null, Text::_('JINVALID_TOKEN'), true);
		}
		else
		{
			$app   = Factory::getApplication();
			$input = $app->input;

			$user            = Factory::getUser();
			$currentDateTime = Factory::getDate()->toSql();

			$activityId = $input->get('activityId', 0, 'INT');
			$licenseId  = $input->get('licenseId', 0, 'INT');

			$slaSlaActivity = SlaSlaActivity::getInstance($activityId);

			if (empty($slaSlaActivity->id) || empty($licenseId))
			{
				echo new JResponseJson(null, Text::_('JERROR_ALERTNOAUTHOR'), true);

				return;
			}

			$model = $this->getModel('SlaActivity', 'SlaModel');

			$deleteActivity = $model->delete($activityId);

			if (!$deleteActivity)
			{
				echo new JResponseJson(null, Text::_('JERROR_ALERTNOAUTHOR'), true);

				return;
			}

			JLoader::import('components.com_jlike.tables.todos', JPATH_ADMINISTRATOR);
			$todoTable = JTable::getInstance('Todos', 'JlikeTable');
			$todoTable->delete($slaSlaActivity->todo_id);

			// Add code to delete timelogs

			echo new JResponseJson(null, Text::_('COM_SLA_ACTIVITY_DELETED_SUCCESSFULLY'), false);

			return;
		}
	}

	/**
	 * Method to get user list depending on the client chosen.
	 *
	 * @return   null
	 *
	 * @since    1.0.0
	 */
	public function getUsersByClusterId()
	{
		$licenseId = Factory::getApplication()->input->getInt('license', 0);
		$app = Factory::getApplication();

		if (!Session::checkToken())
		{
			$app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
			echo new JsonResponse(null, null, true);
			$app->close();
		}

		if (!$licenseId)
		{
			echo new JsonResponse(null, Text::_("COM_SLA_ACTIVITY_LICENSE_NOT_SELECTED"), true);
			$app->close();
		}

		// Get client id by licence id
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_multiagency/tables');
		$licenceTable = Table::getInstance('Licence', 'MultiagencyTable');
		$licenceTable->load(array('id' => $licenseId));
		$clientId = $licenceTable->multiagency_id;

		// Get all users from cluster
		$subusersModelUsers = RBACL::model('users');
		$subusersModelUsers->setState('filter.client_id', $clientId);
		$subusersModelUsers->setState('group_by', 'user_id');
		$subusersModelUsers->setState('filter.state', 0);
		$userOptions = $allUsers = array();
		$allUsers = $subusersModelUsers->getItems();

		$userOptions[] = HTMLHelper::_('select.option', "", Text::_('COM_SLA_SELECT_USER'));

		if (!empty($allUsers))
		{
			foreach ($allUsers as $user)
			{
				$userOptions[] = HTMLHelper::_('select.option', $user->user_id, trim($user->name));
			}
		}

		echo new JsonResponse($userOptions);
		jexit();
	}
}
