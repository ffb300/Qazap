<?php
/**
 * emailtemplate.php
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

jimport('joomla.application.component.modeladmin');

/**
* Qazap model.
*/
class QazapModelEmailtemplate extends JModelAdmin
{
	/**
	* @var		string	The prefix to use with controller messages.
	* @since	1.0.0
	*/
	protected $text_prefix = 'COM_QAZAP';


	/**
	* Returns a reference to the a Table object, always creating it.
	*
	* @param	type	The table type to instantiate
	* @param	string	A prefix for the table class name. Optional.
	* @param	array	Configuration array for model. Optional.
	* @return	JTable	A database object
	* @since	1.0.0
	*/
	public function getTable($type = 'Emailtemplate', $prefix = 'QazapTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_qazap.emailtemplate', 'emailtemplate', array('control' => 'jform', 'load_data' => $loadData));


		if (empty($form)) 
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.emailtemplate.data', array());

		if (empty($data)) 
		{
			$data = $this->getItem();

		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.0.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) 
		{
			//Do any procesing on fields here if needed
		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.0.0
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) 
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '') 
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_emailtemplates');
				$max = $db->loadResult();
				$table->ordering = $max+1;                                                 
			}
		}
	}
	
	public function save($data)	
	{		
		$table = $this->getTable();

		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;
		
		try
		{
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}
			
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapsystem');
			
			// Trigger the onEventBeforeSave event.
			$result = $dispatcher->trigger('onBeforeSave', array('emailTemplate', &$data, $isNew));
			
			if (!$table->bind($data))
			{
				$this->setError($table->getError());
				return false;
			}
			
			$this->prepareTable($table);

			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}			 
			

			
			if (in_array(false, $result, true))
			{
				$this->setError($table->getError());
				return false;
			}	
			
			if(!$default = $table->checkSetDefault())
			{
				$this->setError($table->getError());
				return false;
			}			
			
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}

			$this->cleanCache();
			
			// Trigger the onEventBeforeSave event.
			$result = $dispatcher->trigger('onAfterSave', array('emailTemplate', $data, $isNew));

		}
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}

		$pkName = $table->getKeyName();
		
		if (isset($table->$pkName)) 
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
		}
		
		$this->setState($this->getName() . '.new', $isNew);
		return true;
	}
	
	
	public function setDefault(&$pks, $value = 1)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$user = JFactory::getUser();
		$table = $this->getTable();
		$pks = (array) $pks;

		if (empty($pks))
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
			return false;
		}
		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin('content');

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk))
			{
				if (!$this->canEditState($table))
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');

					return false;
				}
				
				// Attempt to change the state of the records.
				if (!$table->setDefault($pk, $value, $user->get('id')))
				{
					$this->setError($table->getError());
					return false;
				}
				
			}
		}

		$context = $this->option . '.' . $this->name;

		// Trigger the onContentChangeState event.
		$result = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());
			return false;
		}
		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
	
	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0.0
	 */
	public function publish(&$pks, $value = 1)
	{		
		if($value == 0)
		{
			$table = $this->getTable();
			$defaults = $table->checkDefault($pks);
			
			if($defaults === false)
			{
				$this->setError($table->getError());
				return false;
			}
			elseif(!empty($defaults))
			{
				if(!empty($pks))
				{
					JFactory::getApplication()->enqueueMessage('You can not unpublish a default item');
				}
				else
				{
					JLog::add(JText::_('You can not unpublish a default item'), JLog::WARNING, 'jerror');
					return false;
				}				
			}
		}
		
		return parent::publish($pks, $value);	
	}	
	
	public function getTemplates()
	{
		$templates = array(
			'vendor.new' 						=> JText::_('COM_QAZAP_EMAIL_VENDOR_NEW'),
			'vendor.statuschange' 	=> JText::_('COM_QAZAP_EMAIL_VENDOR_STATUS_CHANGE'),
			'vendor.update' 				=> JText::_('COM_QAZAP_EMAIL_VENDOR_UPDATE'),
			'payment.vendor' 				=> JText::_('COM_QAZAP_EMAIL_VENDOR_PAYMENT'),
			'product.new' 					=> JText::_('COM_QAZAP_EMAIL_PRODUCT_ADDED'),								
			'product.statuschange' 	=> JText::_('COM_QAZAP_EMAIL_PRODUCT_STATUS_CHANGE'),
			'product.update' 				=> JText::_('COM_QAZAP_EMAIL_PRODUCT_UPDATED'),
			'product.delete' 				=> JText::_('COM_QAZAP_EMAIL_PRODUCT_DELETED'),
			'order.new' 						=> JText::_('COM_QAZAP_EMAIL_NEW_ORDER_TO_VENDOR'),
			'order.status' 					=> JText::_('COM_QAZAP_EMAIL_ORDER_STATUS_TO_VENDOR'),
			'order.invoice' 				=> JText::_('COM_QAZAP_EMAIL_ORDER_INVOICE_TO_VENDOR'),
			'ordergroup.new' 				=> JText::_('COM_QAZAP_EMAIL_NEW_ORDER_MAIL_TO_BUYER'),
			'ordergroup.status' 		=> JText::_('COM_QAZAP_EMAIL_ORDER_STATUS_TO_BUYER'),
			'ordergroup.invoice' 		=> JText::_('COM_QAZAP_EMAIL_ORDER_INVOICE_TO_BUYER'),
			'member.new'						=> JText::_('COM_QAZAP_EMAIL_MEMBERSHIP_ACTIVATION'),
			'member.notify'					=> JText::_('COM_QAZAP_EMAIL_MEMBERSHIP_EXPIRATION_ALERT'),
			'member.update'					=> JText::_('COM_QAZAP_EMAIL_MEMBERSHIP_UPDATE'),
			'member.delete'					=> JText::_('COM_QAZAP_EMAIL_MEMBERSHIP_EXPIRE'),								
			'notify.verification' 	=> JText::_('COM_QAZAP_EMAIL_VERIFICATION'),
			'notify.mail' 					=> JText::_('COM_QAZAP_EMAIL_NOTIFICATION_MAIL'),
			'question.product' 			=> JText::_('COM_QAZAP_EMAIL_PRODUCT_RELATED_QUERY'),					
		);
		
		return $templates;
	}

}