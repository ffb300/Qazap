<?php
/**
 * view.json.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Site
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Qazap.
 */
class QazapViewDownload extends JViewLegacy
{
	protected $download;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->download				= $this->get('Download');

    // Check for errors.
    if (count($errors = $this->get('Errors'))) 
    {
			throw new Exception(implode("\n", $errors));
    }
    
    $this->setLayout('jsondefault');
	
		parent::display($tpl);
	} 
    	
}
