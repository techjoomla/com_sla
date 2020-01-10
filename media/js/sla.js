/*
 * @package    Com_Sla
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
var sla = {
	updateTodo: function (obj,todoId) {

		var formData = {};
		formData['todoId']     = todoId;
		formData['todoStatus'] = obj.value;

		var promise = slaService.updateTodo(formData);

		promise.fail(
			function(response) {
				var messages = { "error": [response.responseText]};
				Joomla.renderMessages(messages);
			}
		).done(function(response) {
			if (!response.success && response.message)
			{
				var messages = { "error": [response.message]};
				Joomla.renderMessages(messages);
			}

			if (response.messages){
				Joomla.renderMessages(response.messages);
			}

			if (response.success) {
				var messages = { "success": [response.message]};
				Joomla.renderMessages(messages);
			}
		});
	},
	deleteActivity: function(obj, activityId, licenseId) {

		if(!confirm(Joomla.JText._('COM_SLA_ACTIVITY_CONFIRM_DELETE')))
		{
			return false;
		}

		var formData = {};
		formData['activityId'] = activityId;
		formData['licenseId']  = licenseId;

		var promise = slaService.deleteActivity(formData);

		promise.fail(
			function(response) {
				var messages = { "error": [response.responseText]};
				Joomla.renderMessages(messages);
			}
		).done(function(response) {
			if (!response.success && response.message)
			{
				var messages = { "error": [response.message]};
				Joomla.renderMessages(messages);
			}

			if (response.messages){
				Joomla.renderMessages(response.messages);
			}

			if (response.success) {
				var messages = { "success": [response.message]};
				Joomla.renderMessages(messages);

				jQuery(obj).closest('tr').remove();
			}
		});
	}
}
