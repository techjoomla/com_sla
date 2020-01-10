<?php
/**
 * @package    Sla
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * The sla activity controller
 *
 * @since  1.0.0
 */
class SlaControllerSlaActivity extends FormController
{
	/**
	 * Method to save activity record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   1.0
	 */
	public function save($key = 'id', $urlVar = 'id')
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel();

		$recordId = $this->input->getInt('id');

		// Get the activity data.
		$data = $app->input->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm($data, false);

		if (!$form)
		{
			throw new \Exception($model->getError(), 500);
		}

		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			if (!empty($errors))
			{
				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}
			}

			// Redirect back to the registration screen.
			$this->setRedirect(Route::_('index.php?option=com_sla&view=slaactivity' . $this->getRedirectToItemAppend($recordId, $urlVar), false));

			return false;
		}

		$validData['prev_due_date'] = $data['prev_due_date'];
		$slaSlaActivity = $model->save($validData);

		// Check for errors.
		if (empty($slaSlaActivity->id))
		{
			$this->setMessage(Text::_('COM_SLA_ACTIVITY_SAVE_FAILED'), 'warning');
		}
		else
		{
			$this->setMessage(Text::_('COM_SLA_ACTIVITY_ADDED_SUCCESSFULLY'), 'success');
		}

		// Redirect to the list screen.
		$redirectURl = 'index.php?option=com_sla&view=slaactivity&layout=edit&tmpl=component&id=' . $slaSlaActivity->id;
		$this->setRedirect(Route::_($redirectURl . '&licence_id=' . $validData['license_id'], false));

		return true;
	}
}
