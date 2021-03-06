<?php
/**
 * manufacturers.php
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

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelManufacturers extends JModelList 
{

	/**
	* Constructor.
	*
	* @param    array    An optional associative array of configuration settings.
	* @see        JController
	* @since    1.0.0
	*/
	public function __construct($config = array()) 
	{
		if (empty($config['filter_fields'])) 
		{
			$config['filter_fields'] = array(
			'id', 'a.id',
			'ordering', 'a.ordering',
			'state', 'a.state',
			'manufacturer_name', 'a.manufacturer_name',
			'manufacturer_email', 'a.manufacturer_email',
			'description', 'a.description',
			'manufacturer_url', 'a.manufacturer_url',
			);
		}

		parent::__construct($config);
	}

	/**
	* Method to auto-populate the model state.
	*
	* Note. Calling getState in this method will result in recursion.
	*/
	protected function populateState($ordering = null, $direction = null) 
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);



		// Load the parameters.
		$params = JComponentHelper::getParams('com_qazap');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.ordering', 'asc');
	}

	/**
	* Method to get a store id based on model configuration state.
	*
	* This is necessary because the model is used by the component and
	* different modules that might need different sets of data or different
	* ordering requirements.
	*
	* @param	string		$id	A prefix for the store id.
	* @return	string		A store id.
	* @since	1.0.0
	*/
	protected function getStoreId($id = '') 
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	* Build an SQL query to load the list data.
	*
	* @return	JDatabaseQuery
	* @since	1.0.0
	*/
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
		$this->getState(
					'list.select', 'a.id,a.manufacturer_name,a.manufacturer_email,a.manufacturer_url,a.ordering,a.state,a.checked_out,a.checked_out_time,b.name AS editor'
					)
		);
		$query->from('`#__qazap_manufacturers` AS a');
		$query->leftjoin('`#__users` AS b ON a.checked_out = b.id');
		$query->group('a.id,a.manufacturer_name,a.manufacturer_email,a.manufacturer_url,a.ordering,a.state');



		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) 
		{
			$query->where('a.state = '.(int) $published);
		} 
		else if ($published === '') 
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		// Filter the items over the search string if set.
		if ($this->getState('filter.search') !== '')
		{
		// Escape the search token.
		$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');

		// Compile the different search clauses.
		$searches = array();
		$searches[] = 'a.manufacturer_name LIKE ' . $token;
		$searches[] = 'a.manufacturer_email LIKE ' . $token;
		$searches[] = 'a.manufacturer_url LIKE ' . $token;

		// Add the clauses to the query.
		$query->where('(' . implode(' OR ', $searches) . ')');
	}



	// Add the list ordering clause.
	$orderCol = $this->state->get('list.ordering');
	$orderDirn = $this->state->get('list.direction');
	if ($orderCol && $orderDirn) 
	{
		$query->order($db->escape($orderCol . ' ' . $orderDirn));
	}

	return $query;
	}

	public function getItems() 
	{
		$items = parent::getItems();
		return $items;
	}

}
