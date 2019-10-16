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

/**
 * This controller provides functionality
 * that helps to payment plugins to prepare their payment data.
 *
 * @package        Crowdfunding
 * @subpackage     Payments
 */
class CrowdfundingControllerPayments extends JControllerLegacy
{
    protected $app;
    protected $log;

    protected $paymentProcessContext;
    protected $paymentProcess;

    protected $projectId;

    protected $text_prefix = 'COM_CROWDFUNDING';

    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->app    = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $this->option = $this->input->getCmd('option');

        // Prepare logger object.
        $file = JPath::clean($this->app->get('log_path') . DIRECTORY_SEPARATOR . 'com_crowdfunding.php');

        $this->log = new Prism\Log\Log();
        $this->log->addAdapter(new Prism\Log\Adapter\Database(JFactory::getDbo(), '#__crowdf_logs'));
        $this->log->addAdapter(new Prism\Log\Adapter\File($file));
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return   CrowdfundingModelPayments    The model.
     * @since    1.5
     */
    public function getModel($name = 'Payments', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function doCapture()
    {
        // Get component parameters
        $params = JComponentHelper::getParams('com_crowdfunding');
        /** @var $params Joomla\Registry\Registry */

        // Check for disabled payment functionality
        if ($params->get('debug_payment_disabled', 0)) {
            throw new RuntimeException(JText::_($this->text_prefix . '_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE'));
        }

        $cid = $this->input->get('cid', array(), 'array');
        $cid = Joomla\Utilities\ArrayHelper::toInteger($cid);

        $messages = array();

        // Trigger the event
        try {
            if (count($cid) > 0) {
                $options = array(
                    'ids' => $cid,
                    'txn_status' => 'pending'
                );

                $items = new Crowdfunding\Transaction\Transactions(JFactory::getDbo());
                $items->load($options);

                if (count($items) === 0) {
                    throw new UnexpectedValueException(JText::_($this->text_prefix . '_ERROR_INVALID_TRANSACTIONS'));
                }

                // Import Crowdfunding Payment Plugins
                $dispatcher = JEventDispatcher::getInstance();
                JPluginHelper::importPlugin('crowdfundingpayment');

                foreach ($items as $item) {
                    $item    = Joomla\Utilities\ArrayHelper::toObject($item);

                    $context = $this->option . '.payments.capture.' . $item->service_alias;

                    // Trigger onContentPreparePayment event.
                    $results = $dispatcher->trigger('onPaymentsCapture', array($context, &$item, &$params));

                    foreach ($results as $message) {
                        if ($message !== null && is_array($message)) {
                            $messages[] = $message;
                        }
                    }
                }
            }
        } catch (UnexpectedValueException $e) {
            $this->setMessage($e->getMessage(), 'notice');
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=transactions', false));
            return;
        } catch (Exception $e) {
            // Store log data in the database
            $this->log->add(
                JText::_($this->text_prefix . '_ERROR_SYSTEM'),
                'CONTROLLER_PAYMENTS_DOCAPTURE_ERROR',
                $e->getMessage()
            );

            throw new RuntimeException(JText::_($this->text_prefix . '_ERROR_SYSTEM'));
        }

        // Set messages.
        if (count($messages) > 0) {
            foreach ($messages as $message) {
                $this->app->enqueueMessage($message['text'], $message['type']);
            }
        }

        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=transactions', false));
    }

    public function doVoid()
    {
        // Get component parameters
        $params = JComponentHelper::getParams('com_crowdfunding');
        /** @var $params Joomla\Registry\Registry */

        // Check for disabled payment functionality
        if ($params->get('debug_payment_disabled', 0)) {
            throw new RuntimeException(JText::_($this->text_prefix . '_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE'));
        }

        $cid = $this->input->get('cid', array(), 'array');
        $cid = Joomla\Utilities\ArrayHelper::toInteger($cid);

        $messages = array();
        
        try {
            if (count($cid) > 0) {
                $options = array(
                    'ids' => $cid,
                    'txn_status' => 'pending'
                );

                $items = new Crowdfunding\Transaction\Transactions(JFactory::getDbo());
                $items->load($options);

                if (count($items) === 0) {
                    throw new UnexpectedValueException(JText::_($this->text_prefix . '_ERROR_INVALID_TRANSACTIONS'));
                }

                // Import Crowdfunding Payment Plugins
                $dispatcher = JEventDispatcher::getInstance();
                JPluginHelper::importPlugin('crowdfundingpayment');

                foreach ($items as $item) {
                    $item    = Joomla\Utilities\ArrayHelper::toObject($item);

                    $context = $this->option . '.payments.void.' . $item->service_alias;

                    // Trigger onContentPreparePayment event.
                    $results = $dispatcher->trigger('onPaymentsVoid', array($context, &$item, &$params));

                    foreach ($results as $message) {
                        if ($message !== null && is_array($message)) {
                            $messages[] = $message;
                        }
                    }
                }
            }
        } catch (UnexpectedValueException $e) {
            $this->setMessage($e->getMessage(), 'notice');
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=transactions', false));

            return;
        } catch (Exception $e) {
            $this->log->add(JText::_($this->text_prefix . '_ERROR_SYSTEM'), 'CONTROLLER_PAYMENTS_DOCAPTURE_ERROR', $e->getMessage());
            throw new RuntimeException(JText::_($this->text_prefix . '_ERROR_SYSTEM'));
        }

        // Set messages.
        if (count($messages) > 0) {
            foreach ($messages as $message) {
                $this->app->enqueueMessage($message['text'], $message['type']);
            }
        }

        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=transactions', false));
    }
}
