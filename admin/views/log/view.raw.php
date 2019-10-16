<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class CrowdfundingViewLog extends JViewLegacy
{
    protected $state;
    protected $item;

    protected $output;

    public function display($tpl = null)
    {
        $this->state = $this->get('State');

        $layout = $this->getLayout();

        switch ($layout) {
            case 'preview':
                $this->item = $this->get('Item');
                break;

            case 'file':
                $app = JFactory::getApplication();
                /** @var $app JApplicationAdministrator */

                $file = $app->input->get('file', '', 'raw');
                if (!empty($file)) {
                    $model        = $this->getModel();
                    $this->output = $model->loadLogFile($file);
                }

                break;
        }

        parent::display($tpl);
    }
}
