<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="row-fluid">
    <div class="span6 form-horizontal">
        <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm"
              id="adminForm" class="form-validate" enctype="multipart/form-data">

            <fieldset>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('data'); ?></div>
                    <div class="controls">

                        <div class="fileupload fileupload-new" data-provides="fileupload">
                            <span class="btn btn-file">
                                <span class="fileupload-new"><i class="icon-folder-open"></i> <?php echo JText::_('COM_CROWDFUNDING_SELECT_FILE'); ?></span>
                                <span class="fileupload-exists"><i class="icon-edit"></i> <?php echo JText::_('COM_CROWDFUNDING_CHANGE'); ?></span>
                                <?php echo $this->form->getInput('data'); ?>
                            </span>
                            <span class="fileupload-preview"></span>
                            <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none">×</a>
                        </div>
                    </div>
                </div>

                <?php
                if (!in_array($this->importType, ['locations', 'regions'], true)) {
                    echo $this->form->renderField('reset_id');
                }

                echo $this->form->renderField('remove_old');

                if (in_array($this->importType, ['locations', 'regions'], true)) {
                    echo $this->form->renderField('country');
                }

                if (strcmp($this->importType, 'locations') === 0) {
                    echo $this->form->renderField('minimum_population');
                } ?>

            </fieldset>

            <input type="hidden" name="task" value="" id="task"/>
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>

<div class="alert alert-info">
    <i class="icon icon-info"></i>
    <?php
    echo $this->resourcesInformation;

    if ($this->importType === 'locations') {?>
    <ul>
        <li><a href="http://download.geonames.org/export/dump/cities1000.zip" download>cities1000.zip</a></li>
        <li><a href="http://download.geonames.org/export/dump/cities5000.zip" download>cities5000.zip</a></li>
        <li><a href="http://download.geonames.org/export/dump/cities15000.zip" download>cities15000.zip</a></li>
    </ul>
    <?php
    }

    if ($this->importType === 'regions') {?>
    <ul>
        <li><a href="http://download.geonames.org/export/dump/admin1CodesASCII.txt" download>admin1CodesASCII.txt</a></li>
    </ul>
<?php
} ?>
</div>