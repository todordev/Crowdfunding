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
 * Crowdfunding export controller
 *
 * @package      Crowdfunding
 * @subpackage   Components
 */
class CrowdfundingControllerExport extends JControllerLegacy
{
    public function getModel($name = 'Export', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function download()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $type  = $this->input->get->getCmd('type');
        $model = $this->getModel();

        try {
            switch ($type) {
                case 'locations':
                    $output   = $model->getLocations();
                    $fileName = 'locations.xml';
                    break;

                case 'currencies':
                    $output   = $model->getCurrencies();
                    $fileName = 'currencies.xml';
                    break;

                case 'countries':
                    $output   = $model->getCountries();
                    $fileName = 'countries.xml';
                    break;

                case 'states':
                    $output   = $model->getStates();
                    $fileName = 'states.xml';
                    break;

                default: // Error
                    $output   = '';
                    $fileName = 'error.xml';
                    break;
            }

        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.archive');

        $tmpFolder = JPath::clean($app->get('tmp_path'));

        $date = new JDate();
        $date = $date->format('d_m_Y_H_i_s');

        $archiveName = JFile::stripExt(basename($fileName)) . '_' . $date;
        $archiveFile = $archiveName . '.zip';
        $destination = $tmpFolder . DIRECTORY_SEPARATOR . $archiveFile;

        // compression type
        $zipAdapter   = JArchive::getAdapter('zip');
        $filesToZip[] = array(
            'name' => $fileName,
            'data' => $output
        );

        $zipAdapter->create($destination, $filesToZip, array());

        $filesize = filesize($destination);

        $app = JFactory::getApplication();
        
        $app->setHeader('Content-Type', 'application/octet-stream', true);
        $app->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $app->setHeader('Content-Transfer-Encoding', 'binary', true);
        $app->setHeader('Pragma', 'no-cache', true);
        $app->setHeader('Expires', '0', true);
        $app->setHeader('Content-Disposition', 'attachment; filename=' . $archiveFile, true);
        $app->setHeader('Content-Length', $filesize, true);

        $doc = JFactory::getDocument();
        $doc->setMimeEncoding('application/octet-stream');

        $app->sendHeaders();

        echo file_get_contents($destination);
        $app->close();
    }
}
