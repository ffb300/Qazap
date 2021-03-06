<?php
/**
 * ajax.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Admin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

if(!$this->items)
{
	$selector = '<option value="">' . JText::_('COM_QAZAP_SELECT_NOT_APPLICATION') . '</option>';
	$options = null;
}
else
{
	$selector = '<option value="">' . JText::_('COM_QAZAP_SELECT') . '</option>';
	
	$options = array();	
	foreach($this->items as $item)
	{
		$options[] = '<option value="' . (int) $item->id . '">' . JText::_($item->state_name) . '</option>';
	}
	
	$options = implode($options);		
}

echo json_encode(array('selector' => $selector, 'options' => $options));