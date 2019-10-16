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

/**
 * Crowdfunding users controller
 *
 * @package      Crowdfunding
 * @subpackage   Components
 */
class CrowdfundingControllerUsers extends Prism\Controller\Admin
{
    public function getModel($name = 'User', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function view()
    {
        $cid = $this->input->get('cid', array(), 'array');

        $id  = array_shift($cid);

        $this->setRedirect(JRoute::_('index.php?option=com_crowdfunding&view=user&id='.(int)$id, false));
    }
}
