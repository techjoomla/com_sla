<?php
/**
 * @package    Sla
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('formbehavior.chosen', 'select');

$options['relative'] = true;
HTMLHelper::_('script', 'com_timelog/timelog.js', $options);

$app = Factory::getApplication();
$tmpl      = $app->input->getString('tmpl', '');
$licenseId = $app->input->getInt('licence_id', 0);

// Check template component set or not.
if (!empty($tmpl))
{
	$doc = Factory::getDocument();
	$appendUrl .= '&tmpl=' . $tmpl;
	$doc->addStyleSheet('templates/shaper_helix3/css/bootstrap.min.css');
	$doc->addStyleSheet('templates/shaper_helix3/css/custom.css');
}

Factory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "slaactivity.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
		{
			jQuery("#permissions-sliders select").attr("disabled", "disabled");
			Joomla.submitform(task, document.getElementById("adminForm"));
		}
		else
		{
			jQuery("html, body").animate({ scrollTop: 0 }, "slow");
		}
	};
');

$slaActivityFormLink = 'index.php?option=com_sla&view=slaactivity&layout=edit';
$addSlaActivityLink = Route::_($slaActivityFormLink . '&tmpl=component&licence_id=' . $licenseId);

?>
<div>
	<div class="">
		<div class="">
			<div class="">
				<div class="timelog-add-form activity-edit front-end-edit ml-20 mr-20">
					<?php if (!$this->canDo) : ?>
						<h3>
							<?php throw new Exception(Text::_('COM_TIMELOG_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
						</h3>
					<?php endif; ?>
					<button type="button" class="close" onclick="timeLog.closePopup();">&times;</button>
					<h3 class="activity-header mb-30">
						<?php
							echo (empty($this->item->id)) ? Text::_('COM_SLA_ADD_SLA_ACTIVITY') : Text::_('COM_SLA_EDIT_SLA_ACTIVITY');
						?>

						<?php
						if (!empty($this->item->id))
						{
						?>
						<a style="margin-right: 20px;" class="btn btn-primary btn-small pull-right" href="<?php echo $addSlaActivityLink; ?>">
							<i class="icon-plus"></i>
							<?php echo JText::_('COM_SLA_ADD_MORE_SLA_ACTIVITY'); ?>
						</a>
						<?php
						}
						?>
					</h3>
					<div class="clearfix"></div>
					<form id="adminForm" action="" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

						<?php
							echo $this->form->renderField('license_id');

							echo $this->form->renderField('lead_consultant_id');

							echo $this->form->renderField('sla_activity_type_id');

							echo $this->form->renderField('activity_name');

							echo $this->form->renderField('activity_desc');

							echo $this->form->renderField('ideal_time');

							echo $this->form->renderField('start_date');

							echo $this->form->renderField('due_date');

							echo $this->form->renderField('todo_id');
						?>

						<div class="control-group">
							<div class="controls">
								<?php if ($this->canDo): ?>
									<button onclick="Joomla.submitbutton('slaactivity.save');" type="button" class="btn btn-primary"><?php echo JText::_('JSUBMIT'); ?></button>
								<?php endif; ?>
								<a onclick="timeLog.closePopup();" class="btn btn-default" href="javascript:void(0);" title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>
							</div>
						</div>

						<input type="hidden" name="jform[id]" id="id" value="<?php echo $this->item->id; ?>" />
						<input type="hidden" name="option" value="com_sla"/>
						<input type="hidden" name="task" value="slaactivity.save"/>

						<?php echo JHtml::_('form.token'); ?>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
