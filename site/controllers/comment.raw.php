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
 * Crowdfunding comment controller
 *
 * @package     ITPrism Components
 * @subpackage  Crowdfunding
 */
class CrowdfundingControllerComment extends JControllerLegacy
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    CrowdfundingModelCommentItem|bool    The model.
     * @since    1.5
     */
    public function getModel($name = 'CommentItem', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to load data via AJAX.
     *
     * @throws \Exception
     */
    public function getData()
    {
        // Get the input
        $app    = JFactory::getApplication();
        $itemId = $app->input->getUint('id', 0);
        $userId = (int)JFactory::getUser()->get('id');

        $response = new Prism\Response\Json();

        $model = $this->getModel();
        /** @var $model CrowdfundingModelCommentItem **/

        $item = null;

        try {
            $item = $model->getItem($itemId);

            if (($item !== null) && ($userId !== (int)$item->user_id)) {
                $response
                    ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                    ->setContent(JText::_('COM_CROWDFUNDING_INVALID_PROJECT'))
                    ->failure();

                echo $response;
                $app->close();
            }
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $data = array();
        if (is_object($item)) {
            $data = array(
                'id'      => $item->id,
                'comment' => $item->comment
            );
        }

        $response
            ->setData($data)
            ->success();

        echo $response;
        $app->close();
    }

    /**
     * Method to remove records via AJAX.
     *
     * @throws Exception
     * @return  void
     */
    public function remove()
    {
        $app    = JFactory::getApplication();
        $itemId = $app->input->post->getInt('id', 0);
        $userId = JFactory::getUser()->get('id');

        $response = new Prism\Response\Json();

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdfundingModelCommentItem */

        try {
            $item = $model->getItem($itemId);

            if (($item !== null) && ((int)$item->user_id !== (int)$userId)) {
                $response
                    ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                    ->setContent(JText::_('COM_CROWDFUNDING_COMMENT_CANNOT_REMOVED'))
                    ->failure();

                echo $response;
                $app->close();
            }

            $model->remove($itemId, $userId);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setContent(JText::_('COM_CROWDFUNDING_COMMENT_REMOVED_SUCCESSFULLY'))
            ->success();

        echo $response;
        $app->close();
    }
}
