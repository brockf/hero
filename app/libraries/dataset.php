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
* @package Electric Publisher

*/
class Dataset {
	var $columns;
	var $base_url;
	var $filters;
	var $filter_values;
	var $rows_per_page;
	var $offset;
	var $data;
	var $actions;
	var $params;
	var $data_model;
	var $data_function;

    function __construct() {
        $this->rows_per_page = 50;
    }
    
    /**
    * Initialize Dataset
    *
    * Initializes the dataset with previously set data configuration, column specs
    *
    * @return bool True upon successful initialization
    */
    function initialize () {
    	$CI =& get_instance();
    	
    	$CI->load->library('asciihex');
    	
    	$has_filters = ($CI->uri->segment(5) != '' or (strlen($CI->uri->segment(4)) > 10)) ? '1' : '0';
    	
    	// get filter values
		$this->filter_values = ($has_filters) ? unserialize(base64_decode($CI->asciihex->HexToAscii($CI->uri->segment(4)))) : false;
		
		// get data
    	$params = array();
    	
    	// limit to the rows_per_page configuration
    	$params['limit'] = $this->rows_per_page;
    	
    	// calculate offset
    	$this->offset = ($has_filters) ? $CI->uri->segment(5) : $CI->uri->segment(4);
    	$this->offset = (empty($this->offset)) ? '0' : $this->offset;
    	
    	$params['offset'] = $this->offset;
    	
    	if ($this->filters == true) {
    		foreach ($this->columns as $column) {
    			if ($column['filters'] == true) {
    				if (($column['type'] == 'select' or $column['type'] == 'text' or $column['type'] == 'id') and isset($this->filter_values[$column['filter_name']])) {
    					$filter_params[$column['filter_name']] = $this->filter_values[$column['filter_name']];
    				}
    				elseif ($column['type'] == 'date' and (isset($this->filter_values[$column['filter_name'] . '_start']) or isset($this->filter_values[$column['filter_name'] . '_end']))) {
    					$filter_params[$column['field_start_date']] = (empty($this->filter_values[$column['filter_name'] . '_start'])) ? '2009-01-01' : $this->filter_values[$column['filter_name'] . '_start'];
    					$filter_params[$column['field_end_date']] = (empty($this->filter_values[$column['filter_name'] . '_end'])) ? '2020-12-31' : $this->filter_values[$column['filter_name'] . '_end'];
    				}
    			}
    		}
    		reset($this->columns);
    	}
    	
    	$params = (!empty($filter_params)) ? array_merge($params, $filter_params) : $params;
    	
    	// if we have default parameters to pass to the method, we'll add them here
    	// they are set with the $this->data_source() method
    	$params = array_merge($params, $this->default_data_filters);
    	
    	// calculate parameters without limits
    	$params_no_limits = array();
	    $params_no_limits = (!empty($filter_params)) ? $filter_params : $params_no_limits;
    	
    	// get data
    	$CI->load->model($this->data_model,'data_model');
    	$data_function = $this->data_function;
    	
    	// do an XML export?
    	if ($CI->uri->segment(6) == 'export') {
    		// get data without limits
    		$this->data = $CI->data_model->$data_function($params_no_limits);
    		
    		// convert to CSV
			$this->load->library('array_to_csv');
			$this->array_to_csv->input($this->data);
			
			header("Content-type: application/vnd.ms-excel");
  			header("Content-disposition: attachment; filename=export-" . $this->data_function . '-' . date("Y-m-d") . ".csv");
			echo $this->array_to_csv->output();
			die();
    	}
    	else {
    		// get with limits
    		$this->data = $CI->data_model->$data_function($params);
    	}
    	
    	// calculate total rows if they weren't passed
    	if (empty($this->total_rows)) {
    		// they didn't pass the total_rows via total_rows()
	    	$total_rows = count($CI->data_model->$data_function($params_no_limits));
	    	
	    	// save total rows
    		$this->total_rows = $total_rows;
    	}
    	
    	// store in a public variable
		$this->params = $params;
		
    	// set $url_filters if they exist
    	$url_filters = (!empty($this->filter_values)) ? '/' . $CI->asciihex->AsciiToHex(base64_encode(serialize($this->filter_values))) . '/' : '';
		
		// build pagination
		$config['base_url'] = $this->base_url;
		$config['total_rows'] = $this->total_rows;
		$config['per_page'] = $this->rows_per_page;
		$config['uri_segment'] = ($has_filters) ? 5 : 4;
		$config['num_links'] = '10';
		
		$CI->load->library('pagination');
		$CI->pagination->initialize($config);
		
		return TRUE; 
    }
    
    /*
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
    
    /*
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
    
    /*
    * Define visible Dataset columns
    *
    * Defines the columns with width, sort, filtering, name
    *
    * @param array $columns The columns
    *
    * @return boolean TRUE
    */
    function columns ($columns = array()) {
    	// prep columns
    	// possible types: "id", "date", "select", "text"
	    foreach ($columns as $column) {
	    	$this->columns[] = array(
	    					'name' => $column['name'],
	    					'sort_column' => isset($column['sort_column']) ? $column['sort_column'] : false,
	    					'width' => isset($column['width']) ? $column['width'] : false,
	    					'type' => isset($column['type']) ? $column['type'] : false,
	    					'filters' => (!isset($column['filter'])) ? false : true,
	    					'filter_name' => (!isset($column['filter'])) ? false : $column['filter'],
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
	    	
	    	// so do we have filters?			
	    	if (isset($column['filter'])) {
	    		$this->filters = true;
	    	}	
    	}
    	reset($this->columns);
    	
    	return TRUE;
    }
    
    /*
    * Set Datasource
    *
    * Sets the model and method to retrieve the data from
    *
    * @param string $data_model The model
    * @param string $data_function The method
    *
    * @return boolean TRUE
    */
    function datasource ($data_model, $data_function, $data_filters = array()) {
    	$this->data_model = $data_model;
    	$this->data_function = $data_function;
    	$this->default_data_filters = $data_filters;
    	
    	return TRUE;
    }
    
    /*
    * Total Rows
    *
    * Set total rows so we don't have to try and fish it from the method with a double call
    *
    * @param int $total_rows
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
    * @return bool True upon success
    */
    function action ($name, $link) {
    	$this->actions[] = array(
    							'name' => $name,
    							'link' => $link
    						);
    						
    	return true;
    }
    
    /**
    * Output Table Head
    *
    * Returns the header of the table, including pagination/buttons bar, and form beginning
    *
    * @return string HTML output
    */
    function table_head () {
    	$CI =& get_instance();
    	
    	$output = '';
    	
    	$output .= '<form id="dataset_form" method="get" action="' . $this->base_url . '" rel="' . $this->base_url . '">
    				<div class="pagination">';
    	$output .= $CI->pagination->create_links();
    	
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
    	
    	if ($this->filters == true) {
    		$output .= '<div class="apply_filters"><input type="submit" class="button" name="" value="Filter Dataset" />&nbsp;&nbsp;<input id="reset_filters" type="reset" name="" class="button" value="Clear Filters" />&nbsp;&nbsp;<input id="dataset_export_button" type="button" name="" class="button" value="Export" /></div>';
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
   			$output .= '<td style="width:' . $column['width'] . '">' . $column['name'] . '</td>';
    	}
    	reset($this->columns);
    	
    	$output .= '</tr></thead><tbody>';
    	
    	if ($this->filters == true) {
    		$output .= '<tr class="filters">';
    		
    		// add check_all/uncheck_all checkbox
	    	if (!empty($this->actions)) {
	    		$output .= '<td style="width:5%"><input type="checkbox" name="check_all" id="check_all" value="check_all" /></td>';
	    	}
    		
    		while (list(,$column) = each($this->columns)) {
				if ($column['filters'] == true) {
					$output .= '<td class="filter">';
					
					if ($column['type'] == 'text') {
						$value = (isset($this->filter_values[$column['filter_name']])) ? $this->filter_values[$column['filter_name']] : '';
						$output .= '<input type="text" class="text" name="' . $column['filter_name'] . '" value="' . $value . '" />';
					}
					elseif ($column['type'] == 'id') {
						$value = (isset($this->filter_values[$column['filter_name']])) ? $this->filter_values[$column['filter_name']] : '';
						$output .= '<input type="text" class="text id" name="' . $column['filter_name'] . '" value="' . $value . '" />';
					}
					elseif ($column['type'] == 'date') {
						$value = (isset($this->filter_values[$column['filter_name'] . '_start'])) ? $this->filter_values[$column['filter_name'] . '_start'] : '';
						$output .= '<input type="text" class="text date_start datepick" name="' . $column['filter_name'] . '_start" value="' . $value . '" />';
						
						$value = (isset($this->filter_values[$column['filter_name'] . '_end'])) ? $this->filter_values[$column['filter_name'] . '_end'] : '';
						$output .= '<input type="text" class="text date_end datepick" name="' . $column['filter_name'] . '_end" value="' . $value . '" />';
					}
					elseif ($column['type'] == 'select') {
						$output .= '<select name="' . $column['filter_name'] . '"><option value=""></option>';
						
						foreach ($column['options'] as $value => $name) {
							$selected = (isset($this->filter_values[$column['filter_name']]) and $this->filter_values[$column['filter_name']] == $value) ? ' selected="selected"' : '';
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
    	$CI =& get_instance();
    	
    	$output = '</table>';
    	
    	$output .= '<div class="pagination">';
    	$output .= $CI->pagination->create_links();
    	$output .= '</div></form>
			    	<div class="hidden" id="class">' . $CI->uri->segment(2) . '</div>
					<div class="hidden" id="method">' . $CI->router->fetch_method() . '</div>
					<div class="hidden" id="page">' . $this->offset . '</div>';
    	
    	return $output;
    }
}