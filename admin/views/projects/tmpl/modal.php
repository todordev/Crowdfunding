<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$input = JFactory::getApplication()->input;
$doc   = JFactory::getDocument();

$js = '
jQuery(document).ready(function() {
    jQuery(".js-project-element").on("click", function(event){
        event.preventDefault();

        window.parent.document.getElementById("jform_project_id").value = jQuery(this).data("title");
        window.parent.document.getElementById("jform_project_id_id").value = jQuery(this).data("id");
        window.parent.closeProjectModal();
    });
});';

$doc->addScriptDeclaration($js);
?>
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=projects'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
				<input type="text" name="filter_search" id="filter_search" class="hasTooltip" data-placement="bottom"
					   placeholder="<?php echo JText::_('COM_CROWDFUNDING_SEARCH_IN_TITLE'); ?>"
					   value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					   title="<?php echo JText::_('COM_CROWDFUNDING_SEARCH_IN_PROJECTS_TOOLTIP'); ?>"/>
			</div>
			<div class="btn-group pull-left">
				<button class="btn hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" data-placement="bottom"><i class="icon-search"></i></button>
				<button class="btn hasTooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();" data-placement="bottom"><i class="icon-remove"></i></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable"
					   class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
					<option value="asc" <?php if ($this->listDirn === 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
					<option value="desc" <?php if ($this->listDirn === 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?></option>
				</select>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
					<?php echo JHtml::_('select.options', $this->sortFields, 'value', 'text', $this->listOrder); ?>
				</select>
			</div>

		</div>

	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<table class="table table-striped" id="projectsList">
			<thead><?php echo $this->loadTemplate('head'); ?></thead>
			<tfoot><?php echo $this->loadTemplate('foot'); ?></tfoot>
			<tbody><?php echo $this->loadTemplate('body'); ?></tbody>
		</table>
	<?php endif; ?>

	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="filter_order" value="<?php echo $this->listOrder; ?>" id="filter_order"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->listDirn; ?>"/>
	<input type="hidden" name="layout" value="modal"/>
	<input type="hidden" name="tmpl" value="component"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
