<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

jimport('Prism.init');
jimport('Crowdfunding.init');

class JFormFieldCfproject extends JFormField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'cfproject';

    /**
     * Layout to render
     *
     * @var  string
     */
    protected $layout = 'field.project';

    /**
     * Method to get the field options.
     *
     * @return  mixed   The field option objects.
     * @since   1.6
     * @throws \UnexpectedValueException
     */
    protected function getOptions()
    {
        if (!$this->layout) {
            throw new UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
        }

        return $this->getRenderer($this->layout)->render($this->getLayoutData());
    }

    /**
     * Get the data that is going to be passed to the layout
     *
     * @return  array
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getLayoutData()
    {
        // Get the basic field data
        $data = parent::getLayoutData();

        // Load the current username if available.
        $project = new Crowdfunding\Project(JFactory::getDbo());

        $projectTitle = '';
        if (is_numeric($this->value)) {
            $project->load($this->value);
            $projectTitle = $project->get('title');
        }

        $extraData = array(
            'projectTitle' => $projectTitle
        );

        return array_merge($data, $extraData);
    }
}
