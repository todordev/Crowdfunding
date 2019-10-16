<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingModelProjectItem extends JModelItem implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $items = array();

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  CrowdfundingTableProject|bool  A database object
     * @since   1.6
     */
    public function getTable($type = 'Project', $prefix = 'CrowdfundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since    1.6
     * @throws \Exception
     */
    protected function populateState()
    {
        parent::populateState();

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get the pk of the record from the request.
        $itemId = $app->input->getInt('id');
        $this->setState($this->getName() . '.id', $itemId);

        // Load the parameters.
        $value = $app->getParams($this->option);
        $this->setState('params', $value);
    }

    /**
     * Method to get a single record.
     *
     * @param   int $itemId     The id of the primary key.
     * @param   int $userId The id of the user.
     *
     * @return  stdClass  Object on success, false on failure.
     *
     * @throws  \Exception
     *
     * @since   11.1
     */
    public function getItem($itemId, $userId)
    {
        $storedId = $this->getStoreId($itemId.$userId);

        if (!array_key_exists($storedId, $this->items)) {
            $table = $this->getTable();

            if ($itemId > 0 && $userId > 0) {
                $keys = array(
                    'id'      => $itemId,
                    'user_id' => $userId
                );

                // Attempt to load the row.
                $return = $table->load($keys);

                // Check for a table object error.
                if ($return === false) {
                    throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'));
                }
            }

            // Convert to the JObject before adding other data.
            $properties = $table->getProperties();
            $item       = ArrayHelper::toObject($properties);

            if (property_exists($item, 'params')) {
                $registry = new Registry;
                /** @var  $registry Registry */

                $registry->loadString($item->params);
                $item->params = $registry;
            }

            if ((int)$item->id > 0) {
                $this->prepareAccess($item);
            }

            $this->items[$storedId] = $item;
        }

        return $this->items[$storedId];
    }

    /**
     * Method to prepare access data.
     *
     * @param   stdClass $item
     */
    public function prepareAccess($item)
    {
        $user      = JFactory::getUser();

        $helperBus = new Prism\Helper\HelperBus($item);
        $helperBus->addCommand(new Crowdfunding\Helper\PrepareItemAccessHelper($user));
        $helperBus->handle();
    }

    /**
     * Check if the item can be edited.
     *
     * @param   stdClass $item
     *
     * @return bool
     */
    public function canEdit($item)
    {
        $canEdit = false;

        $user   = JFactory::getUser();

        $userId = (int)$user->get('id');
        $guest  = $user->get('guest');

        // Compute the asset access permissions.
        // Technically guest could edit an article, but lets not check that to improve performance a little.
        if (!$guest) {
            $asset = 'com_crowdfunding.item.' . $item->id;

            // Check general edit permission first.
            if ($userId > 0 && $user->authorise('core.edit', $asset)) {
                $canEdit = true;
            }

            // Now check if edit.own is available.
            if ($userId > 0 && $user->authorise('core.edit.own', $asset)) {
                // Check for a valid user and that they are the owner.
                if ($userId === (int)$item->user_id) {
                    $canEdit = true;
                }
            }
        }

        return $canEdit;
    }

    /**
     * Publish or not an item. If state is going to be published,
     * we have to calculate end date.
     *
     * @param integer $itemId
     * @param integer $userId
     * @param integer $state
     *
     * @throws Exception
     */
    public function saveState($itemId, $userId, $state)
    {
        $keys = array(
            'id'      => $itemId,
            'user_id' => $userId
        );

        // Include plugins to validate content.
        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('content');
        
        /** @var $row CrowdfundingTableProject */
        $row = $this->getTable();
        $row->load($keys);

        // Prepare data only if the user publish the project.
        if ((int)$state === Prism\Constants::PUBLISHED) {
            // Get number of transactions.
            $statistics         = new Crowdfunding\Statistics\Project($this->getDbo(), $row->get('id'));
            $transactionsNumber = (int)$statistics->getTransactionsNumber();

            // If it is not approve and there are no transactions, reset starting date.
            if ($transactionsNumber === 0 && ((int)$row->get('approved') === Prism\Constants::NOT_APPROVED)) {
                $row->set('funding_start', Prism\Constants::DATE_DEFAULT_SQL_DATE);
            }

            $this->prepareTable($row);

            // Validate data.

            // Get component parameters
            $params = JComponentHelper::getParams($this->option);
            /** @var  $params Joomla\Registry\Registry */

            $item    = $row->getProperties();
            $item    = ArrayHelper::toObject($item);

            $context = $this->option . '.projects.changestate';

            // Trigger onContentValidate event.
            $results = $dispatcher->trigger('onContentValidateChangeState', array($context, &$item, &$params, $state));

            // If there is an error, redirect to another page.
            foreach ($results as $result) {
                if ((bool)$result['success'] === false) {
                    throw new RuntimeException(ArrayHelper::getValue($result, 'message'));
                }
            }
        }

        $row->set('published', (int)$state);
        $row->store();

        // Trigger the event

        $context = $this->option . '.project';
        $pks     = array($row->get('id'));

        // Trigger the onContentChangeState event.
        $results    = $dispatcher->trigger('onContentChangeState', array($context, $pks, $state));
        if (in_array(false, $results, true)) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_CHANGE_STATE'));
        }
    }

    /**
     * This method calculates start date and validate funding period.
     *
     * @param CrowdfundingTableProject $table
     *
     * @throws Exception
     */
    protected function prepareTable(&$table)
    {
        // Calculate start and end date if the user publish a project for first time.
        $fundingStartDate = new Prism\Validator\Date($table->get('funding_start'));
        if (!$fundingStartDate->isValid()) {
            $app = JFactory::getApplication();
            /** @var $app JApplicationSite */

            $fundingStart         = new JDate('now', $app->get('offset'));
            $table->set('funding_start', $fundingStart->toSql());

            // If funding type is 'days', calculate end date.
            if ($table->get('funding_days')) {
                $fundingStartDate = new Crowdfunding\Date($table->get('funding_start'));
                $endDate          = $fundingStartDate->calculateEndDate($table->get('funding_days'));
                $table->set('funding_end', $endDate->format(Prism\Constants::DATE_FORMAT_SQL_DATETIME));
            }
        }
    }
}
