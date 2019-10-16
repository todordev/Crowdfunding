<?php
/**
 * @package         Crowdfunding
 * @subpackage      Plugins
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * This plugin send notification mails to the administrator.
 *
 * @package        Crowdfunding
 * @subpackage     Plugins
 */
class plgContentCrowdfundingAdminMail extends JPlugin
{
    protected $errorPrefix = '';

    public function init()
    {
        jimport('Prism.init');
        jimport('Crowdfunding.init');
        jimport('Emailtemplates.init');

        // Load language
        $this->loadLanguage();

        $this->errorPrefix = '[Plugin][Content - Crowdfunding Admin Mail] ';
    }

    /**
     * This method sends notification mail to the administrator when project owner publish a project.
     *
     * If I return NULL, an message will not be displayed in the browser.
     * If I return FALSE, an error message will be displayed in the browser.
     *
     * @param string $context
     * @param array  $ids
     * @param int    $state
     *
     * @return bool|null
     */
    public function onContentChangeState($context, $ids, $state)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        if (strcmp('com_crowdfunding.project', $context) !== 0) {
            return null;
        }

        // Initialize plugin
        $this->init();

        if (!JComponentHelper::isEnabled('com_emailtemplates')) {
            JLog::add($this->errorPrefix.JText::_('LIB_CROWDFUNDING_EMAIL_TEMPLATES_INSTALLATION'), JLog::WARNING, 'com_crowdfunding');
            return null;
        }

        // Check for enabled option for sending mail.
        // when user publish a project.
        $emailId = $this->params->get('send_when_published', 0);
        if (!$emailId) {
            JLog::add($this->errorPrefix.JText::sprintf('PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_EMAIL_TEMPLATE'), JLog::WARNING, 'com_crowdfunding');
            return null;
        }

        $ids = Joomla\Utilities\ArrayHelper::toInteger($ids);

        if (count($ids) > 0 and (int)$state === Prism\Constants::PUBLISHED) {
            $projects = $this->getProjectsData($ids);

            // Send emails.
            foreach ($projects as $project) {
                $result = $this->sendProjectMail($project, $emailId);

                // Check for error.
                if ($result !== true) {
                    break;
                }
            }
        }

        return true;
    }

    /**
     * This method sends notification mail to the administrator when someone create a project.
     *
     * If I return NULL, an message will not be displayed in the browser.
     * If I return FALSE, an error message will be displayed in the browser.
     *
     * @param string  $context
     * @param stdClass  $project
     * @param boolean $isNew
     *
     * @return null|boolean
     */
    public function onContentAfterSave($context, &$project, $isNew)
    {
        if (strcmp('com_crowdfunding.basic', $context) !== 0) {
            return null;
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        // Initialize plugin
        $this->init();

        if (!JComponentHelper::isEnabled('com_emailtemplates')) {
            JLog::add($this->errorPrefix.JText::_('LIB_CROWDFUNDING_EMAIL_TEMPLATES_INSTALLATION'), JLog::WARNING, 'com_crowdfunding');
            return null;
        }

        // Check for enabled option for sending mail
        // when user create a project.
        $emailId = $this->params->get('send_when_create', 0);
        if (!$emailId) {
            JLog::add($this->errorPrefix.JText::sprintf('PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_EMAIL_TEMPLATE'), JLog::WARNING, 'com_crowdfunding');
            return null;
        }

        // Send email
        if ((is_object($project) and $project->id > 0) and $isNew) {
            $this->sendProjectMail($project, $emailId);
        }

        return true;
    }

    /**
     * This method sends notification mail to the administrator when someone reports a project.
     *
     * If I return NULL, an message will not be displayed in the browser.
     * If I return FALSE, an error message will be displayed in the browser.
     *
     * @param string  $context
     * @param stdClass  $report
     *
     * @return null|boolean
     */
    public function onContentAfterReport($context, $report)
    {
        if (strcmp('com_crowdfunding.report', $context) !== 0) {
            return null;
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        // Initialize plugin
        $this->init();

        if (!JComponentHelper::isEnabled('com_emailtemplates')) {
            JLog::add($this->errorPrefix.JText::_('LIB_CROWDFUNDING_EMAIL_TEMPLATES_INSTALLATION'), JLog::WARNING, 'com_crowdfunding');
            return null;
        }

        // Check for enabled option for sending mail
        // when user reports a project.
        $emailId = $this->params->get('send_when_report', 0);
        if (!$emailId) {
            JLog::add($this->errorPrefix.JText::sprintf('PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_EMAIL_TEMPLATE'), JLog::WARNING, 'com_crowdfunding');
            return null;
        }

        // Send email to the administrator.
        if (is_object($report) and $report->id > 0) {
            $this->sendReportMail($report, $emailId);
        }

        return true;
    }

    /**
     * Load data about projects
     *
     * @param array $ids
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    private function getProjectsData($ids)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('a.id, a.title')
            ->select($query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug')
            ->select($query->concatenate(array('b.id', 'b.alias'), ':') . ' AS catslug')
            ->from($db->quoteName('#__crowdf_projects', 'a'))
            ->leftJoin($db->quoteName('#__categories', 'b') . ' ON a.catid = b.id')
            ->where('a.id IN (' . implode(',', $ids) . ')');

        $db->setQuery($query);

        return (array)$db->loadObjectList();
    }

    protected function sendProjectMail(&$project, $emailId)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get website
        $uri     = JUri::getInstance();
        $website = $uri->toString(array('scheme', 'host'));

        $emailMode = $this->params->get('email_mode', 'plain');

        // Prepare data for parsing
        $data = array(
            'site_name'  => $app->get('sitename'),
            'site_url'   => JUri::root(),
            'item_title' => $project->title,
            'item_url'   => $website . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($project->slug, $project->catslug)),
        );

        $email = new Emailtemplates\Email();
        $email->setDb(JFactory::getDbo());
        $email->load($emailId);

        if (!$email->getSenderName()) {
            $email->setSenderName($app->get('fromname'));
        }
        if (!$email->getSenderEmail()) {
            $email->setSenderEmail($app->get('mailfrom'));
        }

        // Prepare recipient data.
        $componentParams = JComponentHelper::getParams('com_crowdfunding');
        /** @var  $componentParams Joomla\Registry\Registry */

        $recipientId = (int)$componentParams->get('administrator_id');
        if ($recipientId > 0) {
            $recipient     = JFactory::getUser($recipientId);
            $recipientName = $recipient->get('name');
            $recipientMail = $recipient->get('email');
        } else {
            $recipientName = $app->get('fromname');
            $recipientMail = $app->get('mailfrom');
        }

        // Prepare data for parsing
        $data['sender_name']     = $email->getSenderName();
        $data['sender_email']    = $email->getSenderEmail();
        $data['recipient_name']  = $recipientName;
        $data['recipient_email'] = $recipientMail;

        $email->parse($data);
        $subject = $email->getSubject();
        $body    = $email->getBody($emailMode);

        $mailer = JFactory::getMailer();
        if (strcmp('html', $emailMode) === 0) { // Send as HTML message
            $result = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_HTML);
        } else { // Send as plain text.
            $result = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_PLAIN);
        }

        // Log the error.
        if ($result !== true) {
            JLog::add($this->errorPrefix.$mailer->ErrorInfo, JLog::WARNING, 'com_crowdfunding');
            return false;
        }

        return true;
    }

    protected function sendReportMail($report, $emailId)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get website
        $uri     = JUri::getInstance();
        $website = $uri->toString(array('scheme', 'host'));

        $emailMode = $this->params->get('email_mode', 'plain');

        // Get project
        $project = new Crowdfunding\Project(JFactory::getDbo());
        $project->load($report->project_id);

        // Prepare data for parsing
        $data = array(
            'site_name'  => $app->get('sitename'),
            'site_url'   => JUri::root(),
            'item_title' => $project->getTitle(),
            'item_url'   => $website . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($project->getSlug(), $project->getCatSlug())),
            'report_subject'     => $report->subject,
            'report_description' => $report->description
        );

        $email = new Emailtemplates\Email();
        $email->setDb(JFactory::getDbo());
        $email->load($emailId);

        if (!$email->getSenderName()) {
            $email->setSenderName($app->get('fromname'));
        }
        if (!$email->getSenderEmail()) {
            $email->setSenderEmail($app->get('mailfrom'));
        }

        // Prepare recipient data.
        $componentParams = JComponentHelper::getParams('com_crowdfunding');
        /** @var  $componentParams Joomla\Registry\Registry */

        $recipientId = (int)$componentParams->get('administrator_id');
        if ($recipientId > 0) {
            $recipient     = JFactory::getUser($recipientId);
            $recipientName = $recipient->get('name');
            $recipientMail = $recipient->get('email');
        } else {
            $recipientName = $app->get('fromname');
            $recipientMail = $app->get('mailfrom');
        }

        // Prepare data for parsing
        $data['sender_name']     = $email->getSenderName();
        $data['sender_email']    = $email->getSenderEmail();
        $data['recipient_name']  = $recipientName;
        $data['recipient_email'] = $recipientMail;

        $email->parse($data);
        $subject = $email->getSubject();
        $body    = $email->getBody($emailMode);

        $mailer = JFactory::getMailer();
        if (strcmp('html', $emailMode) === 0) { // Send as HTML message
            $result = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_HTML);
        } else { // Send as plain text.
            $result = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_PLAIN);
        }

        // Log the error.
        if ($result !== true) {
            JLog::add($this->errorPrefix.$mailer->ErrorInfo, JLog::WARNING, 'com_crowdfunding');
            return false;
        }

        return true;
    }
}
