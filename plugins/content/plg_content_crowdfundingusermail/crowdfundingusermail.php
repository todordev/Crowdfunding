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
 * Crowdfunding User Mail Plugin
 *
 * @package        Crowdfunding
 * @subpackage     Plugins
 */
class plgContentCrowdfundingUserMail extends JPlugin
{
    protected $errorPrefix = '';

    public function init()
    {
        jimport('Prism.init');
        jimport('Crowdfunding.init');
        jimport('Emailtemplates.init');

        // Load language
        $this->loadLanguage();

        $this->errorPrefix = '[Plugin][Content - Crowdfunding User Mail] ';
    }

    /**
     * Send notification mail to a user when his project be approved.
     * If I return NULL, an message will not be displayed in the browser.
     * If I return FALSE, an error message will be displayed in the browser.
     *
     * @param string $context
     * @param array $ids
     * @param int $state
     *
     * @return bool|null
     */
    public function onContentChangeState($context, $ids, $state)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if (!$app->isAdmin()) {
            return null;
        }

        if (strcmp('com_crowdfunding.project', $context) !== 0) {
            return null;
        }

        // Initialize plugin
        $this->init();

        if (!JComponentHelper::isInstalled('com_emailtemplates')) {
            JLog::add($this->errorPrefix.JText::_('LIB_CROWDFUNDING_EMAIL_TEMPLATES_INSTALLATION'), JLog::WARNING, 'com_crowdfunding');
            return null;
        }

        // Check for enabled option for sending mail
        // when administrator approve project.
        $emailId = $this->params->get('send_when_approved', 0);
        if (!$emailId) {
            JLog::add($this->errorPrefix.JText::sprintf('PLG_CONTENT_CROWDFUNDINGUSERMAIL_ERROR_INVALID_EMAIL_TEMPLATE'), JLog::WARNING, 'com_crowdfunding');
            return null;
        }

        $ids = Joomla\Utilities\ArrayHelper::toInteger($ids);
        if (count($ids) > 0 and $state === Prism\Constants::APPROVED) {
            $projects = $this->getProjectsData($ids);

            // Send email to a user.
            foreach ($projects as $project) {
                $return = $this->sendMail($project, $emailId);

                if ($return !== true) {
                    break;
                }
            }
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
            ->select(
                'a.id, a.title, ' .
                'u.name, u.email'
            )
            ->select($query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug')
            ->select($query->concatenate(array('b.id', 'b.alias'), ':') . ' AS catslug')
            ->from($db->quoteName('#__crowdf_projects', 'a'))
            ->leftJoin($db->quoteName('#__categories', 'b') . ' ON a.catid = b.id')
            ->leftJoin($db->quoteName('#__users', 'u') . ' ON a.user_id = u.id')
            ->where('a.id IN (' . implode(',', $ids) . ')');

        $db->setQuery($query);

        return (array)$db->loadObjectList();
    }

    /**
     * @param stdClass $project
     * @param int $emailId
     *
     * @return bool
     */
    protected function sendMail($project, $emailId)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get website
        $uri     = JUri::getInstance();
        $website = $uri->toString(array('scheme', 'host'));

        $emailMode = $this->params->get('email_mode', 'plain');

        // Route project URI
        $appSite   = JApplicationCms::getInstance('site');
        $router    = $appSite->getRouter('site');

        $routedUri = $router->build(CrowdfundingHelperRoute::getDetailsRoute($project->slug, $project->catslug));
        if ($routedUri instanceof JUri) {
            $routedUri = $routedUri->toString();
        }

        if (0 === strpos($routedUri, '/administrator')) {
            $routedUri = str_replace('/administrator', '', $routedUri);
        }

        // Prepare data for parsing
        $data = array(
            'site_name'  => $app->get('sitename'),
            'site_url'   => JUri::root(),
            'item_title' => $project->title,
            'item_url'   => $website . $routedUri,
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

        $recipientName = $project->name;
        $recipientMail = $project->email;

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
