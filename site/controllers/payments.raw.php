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

/**
 * This controller provides functionality
 * that helps to payment plugins to prepare their data.
 *
 * @package        Crowdfunding
 * @subpackage     Payments
 */
class CrowdfundingControllerPayments extends JControllerLegacy
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return   CrowdfundingModelPayments|bool    The model.
     * @since    1.5
     */
    public function getModel($name = 'Payments', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * This method trigger the event onPaymentsPreparePayment.
     * The purpose of this method is to load a data and send it to browser.
     * That data will be used in the process of payment.
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function preparePaymentAjax()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get component parameters
        $params = JComponentHelper::getParams('com_crowdfunding');
        /** @var  $params Joomla\Registry\Registry */

        $response = new Prism\Response\Json();

        // Check for disabled payment functionality
        if ($params->get('debug_payment_disabled', 0)) {
            // Send response to the browser
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE'))
                ->failure();

            echo $response;
            $app->close();
        }

        $paymentResult = null;

        // Prepare payment service alias.
        $filter         = new JFilterInput();
        $paymentService = $filter->clean(strtolower(trim($this->input->getCmd('payment_service'))), 'ALNUM');

        // Trigger the event
        try {
            $context = 'com_crowdfunding.preparepayment.' . $paymentService;

            // Import Crowdfunding Payment Plugins
            $dispatcher = JEventDispatcher::getInstance();
            JPluginHelper::importPlugin('crowdfundingpayment');

            // Trigger onContentPreparePayment event.
            $results = $dispatcher->trigger('onPaymentsPreparePayment', array($context, &$params));

            // Get the result, that comes from the plugin.
            if (is_array($results) && count($results) > 0) {
                foreach ($results as $result) {
                    if ($result !== null && is_object($result)) {
                        $paymentResult = $result;
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');

            // Send response to the browser
            $response
                ->failure()
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));

            echo $response;
            $app->close();
        }

        // Check the response
        $success = isset($paymentResult->success) ? $paymentResult->success : null;
        $title   = isset($paymentResult->title) ? $paymentResult->title : null;
        $text    = isset($paymentResult->text) ? $paymentResult->text : null;
        $data    = isset($paymentResult->data) ? $paymentResult->data : null;

        if (!$success) { // If there is an error...
            $paymentSessionContext = Crowdfunding\Constants::PAYMENT_SESSION_CONTEXT . $this->input->getUint('pid');

            // Initialize the payment process object.
            $paymentSessionLocal        = new JData();
            $paymentSessionLocal->step1 = false;
            $app->setUserState($paymentSessionContext, $paymentSessionLocal);

            // Send response to the browser
            $response
                ->failure()
                ->setTitle($title)
                ->setContent($text);
        } else { // If all is OK...
            $response
                ->success()
                ->setTitle($title)
                ->setContent($text)
                ->setData($data);
        }

        echo $response;
        $app->close();
    }
}
