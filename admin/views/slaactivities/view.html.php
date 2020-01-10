<?php
/**
 * @package    Sla
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Object\CMSObject;

/**
 * Sla activities view
 *
 * @since  1.0.0
 */
class SlaViewSlaActivities extends HtmlView
{
	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  JPagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var  JForm
	 */
	public $filterForm;

	/**
	 * Logged in User
	 *
	 * @var  JObject
	 */
	public $user;

	/**
	 * The active search filters
	 *
	 * @var  array
	 */
	public $activeFilters;

	/**
	 * The sidebar markup
	 *
	 * @var  string
	 */
	protected $sidebar;

	/**
	 * The access varible
	 *
	 * @var  CMSObject
	 *
	 * @since  1.0.0
	 */
	protected $canDo;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		// Get state
		$this->state = $this->get('State');

		// This calls model function getItems()
		$this->items = $this->get('Items');

		// Get pagination
		$this->pagination = $this->get('Pagination');

		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$this->user            = Factory::getUser();
		$this->canDo         = JHelperContent::getActions('com_sla');

		// Add submenu
		SlaHelper::addSubmenu('slaactivities');

		// Add Toolbar
		$this->addToolbar();

		// Set sidebar
		$this->sidebar = JHtmlSidebar::render();

		// Display the view
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    1.0.0
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_SLAS_VIEW_SLA_ACTIVITIES'), '');
		$canDo = $this->canDo;

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('slaactivity.add');
		}

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('slaactivity.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::publish('slaactivities.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('slaactivities.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::archiveList('slaactivities.archive', 'JTOOLBAR_ARCHIVE');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'slaactivities.delete', 'JTOOLBAR_DELETE');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences('com_sla');
			JToolbarHelper::divider();
		}
	}

	/**
	 * Method to order fields
	 *
	 * @return ARRAY
	 */
	protected function getSortFields()
	{
		return array(
			'sa.id' => JText::_('JGRID_HEADING_ID'),
			'sa.sla_id' => JText::_('COM_SLA_LIST_SLA_ACTIVITY_SLA'),
			'sa.sla_service_id' => JText::_('COM_SLA_LIST_SLA_ACTIVITY_SERVICE'),
			'sa.cluster_id' => JText::_('COM_SLA_LIST_SLA_ACTIVITY_SCHOOL'),
			'sa.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'sa.state' => JText::_('JSTATUS'),
		);
	}
}
