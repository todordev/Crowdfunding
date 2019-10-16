<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class CrowdfundingModelNotifier extends JModelLegacy
{
    /**
     * Send mail to administrator and notify him
     * if there is an error during process of transaction.
     * @throws \Exception
     */
    public function sendMailToAdministrator()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Send email to the administrator
        $subject = JText::_('COM_CROWDFUNDING_ERROR_PAYMENT_PROCESS_SUBJECT');
        $body    = JText::_('COM_CROWDFUNDING_ERROR_PAYMENT_PROCESS_BODY');
        $return  = JFactory::getMailer()->sendMail($app->get('mailfrom'), $app->get('fromname'), $app->get('mailfrom'), $subject, $body);

        // Check for an error.
        if ($return !== true) {
            $error = JText::sprintf('COM_CROWDFUNDING_ERROR_MAIL_SENDING_ADMIN');
            JLog::add($error);
        }
    }

    /**
     * Remove a record of payment session from the database.
     *
     * @param stdClass $session
     *
     * @deprecated v2.8
     */
    public function closePaymentSession($session)
    {
        if (is_object($session) && (int)$session->id > 0) {
            $intention = new Crowdfunding\Intention(JFactory::getDbo());

            $intention->setId($session->id);
            $intention->delete();
        }
    }
}
