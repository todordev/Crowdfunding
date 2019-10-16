<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string  $autocomplete   Autocomplete attribute for the field.
 * @var   boolean $autofocus      Is autofocus enabled?
 * @var   string  $class          Classes for the input.
 * @var   string  $description    Description of the field.
 * @var   boolean $disabled       Is this field disabled?
 * @var   string  $group          Group the field belongs to. <fields> section in form XML.
 * @var   boolean $hidden         Is this field hidden in the form?
 * @var   string  $hint           Placeholder for the field.
 * @var   string  $id             DOM id of the field.
 * @var   string  $label          Label of the field.
 * @var   string  $labelclass     Classes to apply to the label.
 * @var   boolean $multiple       Does this field support multiple values?
 * @var   string  $name           Name of the input field.
 * @var   string  $onchange       Onchange attribute for the field.
 * @var   string  $onclick        Onclick attribute for the field.
 * @var   string  $pattern        Pattern (Reg Ex) of value of the form field.
 * @var   boolean $readonly       Is this field read only?
 * @var   boolean $repeat         Allows extensions to duplicate elements.
 * @var   boolean $required       Is this field required?
 * @var   integer $size           Size attribute of the input.
 * @var   boolean $spellcheck     Spellcheck state for the form field.
 * @var   string  $validate       Validation rules to apply.
 * @var   string  $value          Value attribute of the field.
 * @var   array   $checkedOptions Options that will be set as checked.
 * @var   boolean $hasValue       Has this field a value assigned?
 * @var   array   $options        Options available for this field.
 *
 * @var   string  $userName       The user name
 * @var   mixed   $groups         The filtering groups (null means no filtering)
 * @var   mixed   $exclude        The users to exclude from the list of users
 */

$params = array(
    'title' => JText::_('COM_CROWDFUNDING_FORM_SELECT_REWARD'),
    'url'   => 'index.php?option=com_crowdfunding&amp;view=rewards&amp;layout=modal&amp;tmpl=component',
    'height' => 500
);
echo JHtml::_('bootstrap.renderModal', 'js-modal-reward', $params);

$doc = JFactory::getDocument();
$doc->addScript('../media/com_crowdfunding/js/admin/field/reward.js');
?>
<?php // Create a dummy text field with the user name. ?>
<div class="input-append">
    <input
        type="text" id="<?php echo $id; ?>"
        value="<?php echo htmlspecialchars($rewardTitle, ENT_COMPAT, 'UTF-8'); ?>"
        placeholder="<?php echo JText::_('COM_CROWDFUNDING_FORM_SELECT_REWARD'); ?>"
        readonly
        <?php echo $size ? ' size="' . (int)$size . '"' : ''; ?>
        <?php echo $required ? 'required' : ''; ?>/>
    <?php if (!$readonly) : ?>
        <a class="btn btn-primary <?php echo $id; ?>" title="<?php echo JText::_('COM_CROWDFUNDING_FORM_CHANGE_REWARD'); ?>">
            <span class="icon-grid"></span>
        </a>
    <?php endif; ?>
</div>

<?php // Create the real field, hidden, that stored the reward id. ?>
<input type="hidden" id="<?php echo $id; ?>_id" name="<?php echo $name; ?>" value="<?php echo (int)$value; ?>"/>
