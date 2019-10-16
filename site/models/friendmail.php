<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingModelFriendmail extends JModelForm
{
    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     * @since    1.6
     * @throws \Exception
     */
    protected function populateState()
    {
        parent::populateState();

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get the pk of the record from the request.
        $value = $app->input->getUint('id');
        $this->setState($this->getName() . '.id', $value);

        $params = $app->getParams();
        $this->setState('params', $params);
    }

    /**
     * Method to get the profile form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param    array   $data     An optional array of data for the form to interrogate.
     * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return    JForm|bool    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.friendmail', 'friendmail', array('control' => 'jform', 'load_data' => $loadData));
        if (!$form) {
            return false;
        }

        $form->bind($data);

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @throws \Exception
     * @return    mixed    The data for the form.
     * @since    1.6
     */
    protected function loadFormData()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $data = $app->getUserState($this->option . '.edit.friendmail.data', array());
        if (!$data) {
            $item     = $this->getItem();

            // Prepare default content of the form
            if ((int)$item->id > 0) {
                $filter = JFilterInput::getInstance();

                $uri  = JUri::getInstance();
                $host = $filter->clean($uri->getScheme() .'://'. $uri->getHost());

                $link = $host . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($item->slug, $item->catslug));
                $data = array(
                    'id'      => $item->id,
                    'subject' => JText::sprintf('COM_CROWDFUNDING_SEND_FRIEND_DEFAULT_SUBJECT', $item->title),
                    'message' => JText::sprintf('COM_CROWDFUNDING_SEND_FRIEND_DEFAULT_MESSAGE', $link)
                );

                // Set user data
                $user = JFactory::getUser();
                if ((int)$user->get('id') > 0) {
                    $data['sender_name'] = $user->name;
                    $data['sender']      = $user->email;
                }
            }
        }

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @return  stdClass  Object on success, false on failure.
     *
     * @throws  \Exception
     *
     * @since   11.1
     */
    public function getItem()
    {
        $id   = $this->getState($this->getName() . '.id');
        $item = null;

        if ($id > 0) {
                $db = $this->getDbo();
            /** @var $db JDatabaseDriver */

            // Create a new query object.
            $query = $db->getQuery(true);

            // Select the required fields from the table.
            $query->select(
                'a.id, a.title, ' .
                $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug, ' .
                $query->concatenate(array('c.id', 'c.alias'), ':') . ' AS catslug'
            )
                ->from($db->quoteName('#__crowdf_projects', 'a'))
                ->innerJoin($db->quoteName('#__categories', 'c') . ' ON a.catid = c.id')
                ->where('a.id = ' . (int)$id)
                ->where('a.published = ' . Prism\Constants::PUBLISHED)
                ->where('a.approved = ' . Prism\Constants::APPROVED);

            $db->setQuery($query);
            $item = $db->loadObject();
        }

        return $item;
    }

    /**
     * Method to send mail to friend.
     *
     * @param    array    $data    The form data.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function send($data)
    {
        // Send email to the administrator
        $subject   = ArrayHelper::getValue($data, 'subject');
        $body      = ArrayHelper::getValue($data, 'message');
        $from      = ArrayHelper::getValue($data, 'sender');
        $fromName  = ArrayHelper::getValue($data, 'sender_name');
        $recipient = ArrayHelper::getValue($data, 'receiver');

        $return = JFactory::getMailer()->sendMail($from, $fromName, $recipient, $subject, $body);

        // Check for an error.
        if ($return !== true) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_MAIL_SENDING_FRIEND'));
        }
    }
}
