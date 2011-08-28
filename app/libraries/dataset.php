<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Dataset Model 
*
* Creates dataset tables for the control panel, including jQuery-powered filtering
*
* Table data should be sorted through manually in the view by accessing the public $data array.
* Use TableHead and TableClose to output the surrounding HTML.
* Requires jQuery and appropriate CSS.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/
class Dataset {
	var $CI; // CodeIgniter super object
	var $columns; // column data
	var $rows_per_page; // how many rows of data to show per page?
	var $available_filters; // do we have possible filters?
	var $set_filters; // filters set by user
	var $params; // parameters for the data call
	var $params_filters; // data model parameters set by the user's filters
	var $offset; // database offset
	var $data; // data array
	var $actions; // available actions on the dataset
	var $data_model; // model to retrieve data from
	var $data_function; // method to retrieve data from within the model
	var $total_rows; // these are how many rows are in the total dataset, either calculated automatically or given by total_rows()
	var $base_url; // the base URL of the dataset, the basis of all URL strings
	var $pagination; // stores the HTML for pagination links
	var $sort_column;
	var $sort_dir;
	
    function __construct() {
    	$this->CI =& get_instance();
    	$this->CI->load->library('asciihex');
    	$this->CI->load->helper('url');
    	
    	// set defaults
    	$this->base_url = current_url();
        $this->rows_per_page = ($this->CI->config->item('datasets_rows_per_page')) ? $this->CI->config->item('datasets_rows_per_page') : 50;
        
        // stylesheet
		$this->CI->head_assets->stylesheet('css/dataset.css');
    }
    
    /**
    * Set Datasource
    *
    * Sets the model and method to retrieve the data from
    *
    * @param string $data_model The model
    * @param string $data_function The method
    * @param array $data_filters Default filters to pass to the get_X() method (default: none)
    *
    * @return boolean TRUE
    */
    function datasource ($data_model, $data_function, $data_filters = array()) {
    	$this->data_model = $data_model;
    	$this->data_function = $data_function;
    	$this->params_default = $data_filters;
    	
    	return TRUE;
    }
    
    /**
    * Total Rows
    *
    * Set total rows so we don't have to try and fish it from the method with a double call
    *
    * @param int $total_rows
    *
    * @return boolean TRUE;
    */
    function total_rows ($total_rows) {
    	$this->total_rows = $total_rows;
    	
    	return TRUE;
    }
    
    /**
    * Add Action
    *
    * Adds an action button the dataset
    * When clicked, it creates an ASCII'd, base64_encoded, serialized array of all ID's in the checkboxes of the dataset
    *
    * @param string $name The name of the action for the button
    * @param string $link The link (e.g. /customers/delete) to pass the variable to (e.g. /customers/delete/39f32432849340923849234)
    *
    * @return bool TRUE upon success
    */
    function action ($name, $link) {
    	$this->actions[] = array(
    							'name' => $name,
    							'link' => $link
    						);
    						
    	return TRUE;
    }
    
    /**
    * Set Rows Per Page
    *
    * How many rows to show per page?
    *
    * @param int $rows_per_page
    *
    * @return boolean TRUE;
    */
    function rows_per_page ($rows_per_page) {
    	$this->rows_per_page = $rows_per_page;
    }
    
    /**
    * Sets the base URL
    *
    * This URL is used to post all dataset actions to
    *
    * @param string $base_url The URL
    *
    * @return boolean TRUE
    */
    function base_url ($base_url) {
    	$this->base_url = $base_url;
    	
    	return TRUE;
    }
    
    /**
    * Get Filter Array
    *
    * Return the filter URL segment as an array of filters
    *
    * @return array or FALSE if it doesn't exist
    */
    function get_filter_array() {
    	// are we resetting filters?
    	if ($this->CI->input->post('reset_filters')) {
			$this->set_filters = FALSE;
    	}
    	elseif ($this->CI->input->get('filters') and $this->CI->input->get('filters') != '') {
    		$this->set_filters = unserialize(base64_decode($this->CI->asciihex->HexToAscii($this->CI->input->get('filters'))));
    	}
    	else {
    		$this->set_filters = FALSE;
    	}
    	
		if (is_array($this->set_filters)) {
			foreach ($this->set_filters as $key => $value) {
				$this->set_filters[$key] = urldecode($value);
			}
		}
		
		return $this->set_filters;
    }
    
    /**
    * Get Encoded Filters
    *
    * Gets a encoded version of the filters
    *
    * @return string $encoded_filters
    */
    function get_encoded_filters() {
    	return $this->CI->asciihex->AsciiToHex(base64_encode(serialize($this->set_filters)));
    }
    
    /**
    * Get Limit
    *
    * Get the database limit
    *
    * @return int Limit
    */
    function get_limit () {
    	if ($this->CI->input->get('limit') and $this->CI->input->get('limit') != '') {
    		$this->limit = $this->CI->input->get('limit');
    	}
    	else {
    		$this->limit = $this->rows_per_page;	
    	}
    	
    	return $this->limit;
    }
    
    /**
    * Get Offset
    *
    * Are we browsing a page other than page #1?  If so, we have an offset.
    *
    * @return int Offset
    */
    function get_offset() {
    	if ($this->CI->input->get('offset')) {
    		$this->offset = (int)$this->CI->input->get('offset');
    	}
    	else {
    		$this->offset = 0;
    	}
    	
    	return $this->offset;
    }
    
    /**
    * Get Unlimited Params
    *
    * Returns the current parameters without any database limits
    *
    * @return array
    */
    function get_unlimited_parameters () {
    	$params = $this->params;
    	
    	if (isset($params['limit'])) {
    		unset($params['limit']);
    	}
    	
    	if (isset($params['offset'])) {
    		unset($params['offset']);
    	}
    	
    	return $params;
    }
    
    /**
    * Initialize Dataset
    *
    * Initializes the dataset with previously set data configuration, column specs
    *
    * @param boolean $paginate_now Automatically initialize pagination, otherwise this::initialize_pagination() must be invoked after this (default: TRUE)
    *
    * @return boolean TRUE upon successful initialization
    */
    function initialize ($paginate_now = TRUE) {
    	// get filter values
		$this->get_filter_array();
		
		// begin data getting process with an empty parameters array for the model's get_X() method
    	$this->params = array();
    	
    	// limit to the rows_per_page configuration
    	$this->get_limit();
    	$this->params['limit'] = $this->limit;
    	
    	// calculate offset
    	$this->get_offset();
    	$this->params['offset'] = $this->offset;
    	
    	$this->params_filters = array();
    	
    	// sorting?
    	if ($this->CI->input->get('sort_column')) {
    		$this->sort_column = $this->CI->input->get('sort_column');
    		$this->sort_dir = $this->CI->input->get('sort_dir');
    		
    		if (empty($this->sort_dir) or !in_array($this->sort_dir, array('asc','desc'))) {
    			$this->sort_dir = 'asc';
    		}
    	
    		$this->params_filters['sort'] = $this->sort_column;
    		$this->params_filters['sort_dir'] = $this->sort_dir;
    	}
    	
    	if ($this->available_filters == TRUE) {
    		foreach ($this->columns as $column) {
    			if ($column['filters'] == TRUE) {
    				if (($column['type'] == 'select' or $column['type'] == 'text' or $column['type'] == 'id') and isset($this->set_filters[$column['filter_name']])) {
    					$this->params_filters[$column['filter_name']] = $this->set_filters[$column['filter_name']];
    				}
    				elseif ($column['type'] == 'date' and (isset($this->set_filters[$column['filter_name'] . '_start']) or isset($this->set_filters[$column['filter_name'] . '_end']))) {
    					$this->params_filters[$column['field_start_date']] = (empty($this->set_filters[$column['filter_name'] . '_start'])) ? '2009-01-01' : $this->set_filters[$column['filter_name'] . '_start'];
    					$this->params_filters[$column['field_end_date']] = (empty($this->set_filters[$column['filter_name'] . '_end'])) ? '2020-12-31' : $this->set_filters[$column['filter_name'] . '_end'];
    				}
    			}
    		}
    		reset($this->columns);
    	}
    	
    	// for the major data call, we need to combine database parameters, default parameters, and parameters
    	// created by the filters
    	$this->params = array_merge($this->params, $this->params_filters, $this->params_default);
    	
    	// get data with our $this->params
    	$this->CI->load->model($this->data_model,'data_model');
    	$data_function = $this->data_function;
    	
    	// do a CSV export?
    	if ($this->CI->input->get('export') == 'csv') {
    		// get data without limits
    		$unlimited_params = $this->get_unlimited_parameters();
    		$this->data = $this->CI->data_model->$data_function($unlimited_params);
    		
    		// convert to CSV
			$this->CI->load->library('array_to_csv');
			$this->CI->array_to_csv->input($this->data);
			
			header("Content-type: application/vnd.ms-excel");
  			header("Content-disposition: attachment; filename=export-" . $this->data_function . '-' . date("Y-m-d") . ".csv");
			echo $this->CI->array_to_csv->output();
			die();
    	}
    	
		// get data with our parameters
		$this->data = $this->CI->data_model->$data_function($this->params);
    	
    	if ($paginate_now === TRUE) {
    		$this->initialize_pagination();
    	}
		
		return TRUE; 
    }
    
    /**
    * Initialize Pagination
    *
    * Initializes pagination manually.  This is useful if the CP method wants to use the $this->params value generated
    * in this::initialize() to manually pass a total_rows value via this::total_rows()
    *
    * @return boolean TRUE
    *
    */
    function initialize_pagination () {
    	// if we weren't told how many rows are in the dataset yet, we will
    	// calculate them automatically with an unlimited data call
    	if (empty($this->total_rows)) {
    		$data_function = $this->data_function;
    		// they didn't pass the total_rows via total_rows()
    		$unlimited_params = $this->get_unlimited_parameters();
    		$unlimited_data = $this->CI->data_model->$data_function($unlimited_params);
	    	$total_rows = (empty($unlimited_data)) ? 0 : count($unlimited_data);
	    	
	    	// save total rows
    		$this->total_rows = $total_rows;
    	}
    	
    	// initialize pagination
		$config['base_url'] = $this->base_url . '?filters=' . $this->get_encoded_filters() . '&sort_dir=' . $this->sort_dir . '&sort_column=' . $this->sort_column . '&limit=' . $this->limit;
		$config['total_rows'] = $this->total_rows;
		$config['per_page'] = $this->rows_per_page;
		$config['num_links'] = '10';
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'offset';
		
		$this->CI->load->library('pagination');
		$this->CI->pagination->initialize($config);
		
		// build the pagination links
		$this->get_pagination();
		
		return TRUE;
    }
    
    /**
    * Get Pagination
    *
    * Returns the modified pagination links
    *
    * @return string $pagination
    */
    function get_pagination () {
    	$links = $this->CI->pagination->create_links();
		
		// we may have cases of ?& because of CodeIgniter thinking we have universally enabled query strings
		$links = str_replace('?&amp;','?', $links);
		
		$this->pagination = $links;
		
		return $links;
    }
    
    /**
    * Has Filters
    *
    * Do we have active filters?
    *
    * @return boolean
    */
    function has_filters () {
    	if ($this->get_filter_array() !== FALSE) {
    		return TRUE;
    	}
    	else {
    		return FALSE;
    	}
    }
        
    /**
    * Define visible Dataset columns
    *
    * Defines the columns with width, sort, filtering, name
    *
    * @param array $columns The columns
    *
    * @return void
    */
    function columns ($columns = array()) {
    	// prep columns
    	// possible types: "id", "date", "select", "text"
	    foreach ($columns as $column) {
	    	$this->columns[] = array(
	    					'name' => $column['name'],
	    					'sort_column' => isset($column['sort_column']) ? $column['sort_column'] : FALSE,
	    					'width' => isset($column['width']) ? $column['width'] : FALSE,
	    					'type' => isset($column['type']) ? $column['type'] : FALSE,
	    					'filters' => (!isset($column['filter'])) ? FALSE : TRUE,
	    					'filter_name' => (!isset($column['filter'])) ? FALSE : $column['filter'],
	    					'field_start_date' => isset($column['field_start_date']) ? $column['field_start_date'] : '',
	    					'field_end_date' => isset($column['field_end_date']) ? $column['field_end_date'] : '',
	    					'options' => isset($column['options']) ? $column['options'] : array(),
	    				);
	    			
	    	// error checking			
	    	if (isset($column['type']) and $column['type'] == 'date' and isset($column['filter']) and (!isset($column['field_start_date']) or !isset($column['field_end_date']))) {
	    		die(show_error('Unable to create a "date" filter without field_start_date and field_end_date.'));
	    	}
	    	elseif (isset($column['type']) and $column['type'] == 'select' and !isset($column['options'])) {
	    		die(show_error('Unable to create a "select" filter without options.'));
	    	}
	    	
	    	if (isset($column['type']) and $column['type'] == 'date') {
	    		// this is necessary so we know to include the datepicker JS
				if (!defined('INCLUDE_DATEPICKER')) {
					define('INCLUDE_DATEPICKER','TRUE');
				}
	    	}
	    	
	    	// so do we have filters?			
	    	if (isset($column['filter'])) {
	    		$this->available_filters = TRUE;
	    	}	
    	}
    	reset($this->columns);
    	
    	return;
    }
    
    /**
    * Output Table Head
    *
    * Returns the header of the table, including pagination/buttons bar, and form beginning
    *
    * @return string HTML output
    */
    function table_head () {
    	$output = '';
    	
    	$output .= '<form id="dataset_form" method="get" action="' . $this->base_url . '" rel="' . $this->base_url . '">
    				<input type="hidden" id="submit_ready" name="submit_ready" value="" />
    				<input type="hidden" name="limit" value="' . $this->limit . '" />
    				<input type="hidden" id="filters" name="filters" value="' . $this->get_encoded_filters() . '" />
    				<input type="hidden" id="export" name="export" value="" />
    				<input type="hidden" id="sort_column" name="sort_column" value="' . $this->sort_column . '" />
    				<input type="hidden" id="sort_dir" name="sort_dir" value="' . $this->sort_dir . '" />
    				<div class="pagination">';
    	$output .= $this->pagination;
    	
    	$actions = '';
    	$i = 1;
    	
    	// build action buttons
    	if (!empty($this->actions)) {
    		$actions .= 'With selected: ';
    		while (list(,$action) = each($this->actions)) {
    			$actions .= '<input type="button" class="button action_button" rel="' . site_url($action['link']) . '" name="action_' . $i . '" value="' . $action['name'] . '" />&nbsp;';
    			$i++;
    		}
    		
    		$output .= '<div class="dataset_actions">' . $actions . '</div>';
    	}
    	
    	if ($this->available_filters === TRUE) {
    		$output .= '<div class="apply_filters"><input type="submit" class="button tooltip" title="Only show results matching the filter criteria you have entered/selected at the top of the dataset." name="filter_dataset" value="Filter Dataset" />&nbsp;&nbsp;<input id="reset_filters" type="reset" name="reset_filters" class="button tooltip" title="Show all results in this dataset" value="Clear Filters" />&nbsp;&nbsp;<input id="dataset_export_button" type="button" name="" class="button tooltip" title="Export all dataset results (with ALL of their information) to a CSV file.  This file can then be imported into an application like Excel." value="Export" /></div>';
    	}
    	
    	$output .= '</div>
    				<table class="dataset" cellpadding="0" cellspacing="0">
    				<thead><tr>';
    				
    	// add empty header cell if we have checkboxes
    	if (!empty($this->actions)) {
    		$output .= '<td style="width:5%">&nbsp;</td>';
    	}
    	
    	// add column headers
    	while (list($key,$column) = each($this->columns)) {
    		if (isset($column['sort_column']) and !empty($column['sort_column'])) {
    			if ($this->sort_column == $column['sort_column'] and $this->sort_dir == 'asc') {
    				$direction = 'desc';
    				$title = 'sort descending';
    				
    				$post_name = ' <span class="sorting">asc</span>';
    			}
    			else {
    				$direction = 'asc';
    				$title = 'sort ascending';
    				
    				if ($this->sort_column == $column['sort_column']) {
    					$post_name = '<span class="sorting">desc</span>';
    				}
    				else {
    					$post_name = '';
    				}
    			}
    			
    			$column['name'] = '<a class="sort_column tooltip" title="' . $title . '" rel="' . $column['sort_column'] . '" direction="' . $direction . '" href="#">' . $column['name'] . '</a> ' . $post_name;
    			
    		}
    	
   			$output .= '<td style="width:' . $column['width'] . '">' . $column['name'] . '</td>';
    	}
    	reset($this->columns);
    	
    	$output .= '</tr></thead><tbody>';
    	
    	if ($this->available_filters == TRUE) {
    		$output .= '<tr class="filters">';
    		
    		// add check_all/uncheck_all checkbox
	    	if (!empty($this->actions)) {
	    		$output .= '<td style="width:5%"><input type="checkbox" name="check_all" id="check_all" value="check_all" /></td>';
	    	}
    		
    		while (list(,$column) = each($this->columns)) {
				if ($column['filters'] == TRUE) {
					$output .= '<td class="filter">';
					
					if ($column['type'] == 'text') {
						$value = (isset($this->set_filters[$column['filter_name']])) ? $this->set_filters[$column['filter_name']] : '';
						$output .= '<input type="text" class="text" name="' . $column['filter_name'] . '" value="' . $value . '" />';
					}
					elseif ($column['type'] == 'id') {
						$value = (isset($this->set_filters[$column['filter_name']])) ? $this->set_filters[$column['filter_name']] : '';
						$output .= '<input type="text" class="text id" name="' . $column['filter_name'] . '" value="' . $value . '" />';
					}
					elseif ($column['type'] == 'date') {
						$value = (isset($this->set_filters[$column['filter_name'] . '_start'])) ? $this->set_filters[$column['filter_name'] . '_start'] : '';
						
						if (empty($value)) {
							$classes = ' mark_empty ';
						}
						else {
							$classes = '';
						}
						
						$output .= '<input type="text" rel="start date" class="' . $classes . 'text date_start datepick" name="' . $column['filter_name'] . '_start" value="' . $value . '" />';
						
						$value = (isset($this->set_filters[$column['filter_name'] . '_end'])) ? $this->set_filters[$column['filter_name'] . '_end'] : '';
						$output .= '<input type="text" rel="end date" class="' . $classes . 'text date_end datepick" name="' . $column['filter_name'] . '_end" value="' . $value . '" />';
					}
					elseif ($column['type'] == 'select') {
						$output .= '<select name="' . $column['filter_name'] . '"><option value=""></option>';
						
						foreach ($column['options'] as $value => $name) {
							$selected = (isset($this->set_filters[$column['filter_name']]) and $this->set_filters[$column['filter_name']] == $value) ? ' selected="selected"' : '';
							$output .= '<option value="' . $value . '"' . $selected . '>' . $name . '</option>';
						}
						
						$output .= '</select>';
					}
					
					$output .= '</td>';
				}
				else {
					$output .= '<td></td>';
				}
    		}
    		
    		$output .= '</tr>';
    	}
    	
    	return $output;
    }
    
    /**
    * Output Table Close
    *
    * Returns the HTML for the table closure, including bottom pagination div and form ending
    *
    * @return string HTML output
    */
    function table_close () {
    	$output = '</table>';
    	
    	$output .= '<div class="pagination">';
    	$output .= '<div class="dataset_stats"><b>' . $this->total_rows . '</b> records in dataset</div>';
    	$output .= $this->pagination;
    	$output .= '</div></form>
			    	<div class="hidden" id="class">' . $this->CI->uri->segment(2) . '</div>
					<div class="hidden" id="method">' . $this->CI->router->fetch_method() . '</div>
					<div class="hidden" id="page">' . $this->offset . '</div>';
    	
    	return $output;
    }
}