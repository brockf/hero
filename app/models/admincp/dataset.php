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
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher

*/
class Dataset extends CI_Model {
	var $columns;
	var $base_url;
	var $filters;
	var $filter_values;
	var $rows_per_page;
	var $offset;
	var $data;
	var $actions;
	var $params;

    function Dataset() {
        parent::CI_Model();
        
        $this->rows_per_page = 50;
    }
    
    /**
    * Initialize Dataset
    *
    * Initializes the dataset with data configuration, column specs
    *
    * @param string $data_model The model to load which contains the GetData method
    * @param string $data_function The method in the model to call, should return an associative array
    * @param array $columns Array with each column as an array of settings within it.  Requires "name", "width".  Optional: "sort_column", "type", "filter_name", "field_start_date" and "field_end_date" (for date type), and "options" (for select type).
    * @param string $base_url The URL to use for pagination base.  Optional.
    *
    * @return bool True upon successful initialization
    */
    function Initialize ($data_model, $data_function, $columns, $base_url) {
    	$CI =& get_instance();
    	
    	$CI->load->library('asciihex');
    	
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
    	
    	$has_filters = ($CI->uri->segment(5) != '' or (strlen($CI->uri->segment(4)) > 10)) ? '1' : '0';
    	
    	// get filter values
		$this->filter_values = ($has_filters) ? unserialize(base64_decode($CI->asciihex->HexToAscii($CI->uri->segment(4)))) : false;
    	
    	// get data
    	$params = array();
    	
    	$params['limit'] = $this->rows_per_page;
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
    	
    	// do an XML export?
    	if ($CI->uri->segment(6) == 'export') {
    		$xml_params = '';
			while (list($name,$value) = each($params)) {
				// commented out - do we want to keep offset/limit?
				//if ($name != 'limit' and $name !='offset') {
					$xml_params .= "<$name>$value</$name>";
				//}
			}
			reset($params);
    	
			$postfields = '<?xml version="1.0" encoding="UTF-8"?>
<request>
	<authentication>
		<api_id>' . $CI->user->Get('api_id') . '</api_id>
		<secret_key>' . $CI->user->Get('secret_key') . '</secret_key>
	</authentication>
	<type>' . $data_function . '</type>
' . $xml_params . '
</request>';
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
			curl_setopt($ch, CURLOPT_URL,base_url() . 'api');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
			
			$result = curl_exec($ch);
			curl_close($ch);
			
			header("Content-type: text/xml");
			header("Content-length: " . strlen($result));
			header('Content-Disposition: attachment;filename="export.xml"');
			echo $result;
			die();
    	}
    
    	$CI->load->model($data_model,'data_model');
    	
    	$this->data = $CI->data_model->$data_function($params);
    	
    	unset($params);
    	$params = array();
    	
		$params = (!empty($filter_params)) ? array_merge($params, $filter_params) : $params;
		
		// store in a public variable
		$this->params = $params;
    	
    	$total_rows = count($CI->data_model->$data_function($params));
    	
    	$this->total_rows = $total_rows;
    	
    	// set $url_filters if they exist
    	$url_filters = (!empty($this->filter_values)) ? '/' . $CI->asciihex->AsciiToHex(base64_encode(serialize($this->filter_values))) . '/' : '';

		// calculate base_url
		$this->base_url = $base_url;
		
		$config['base_url'] = $this->base_url;
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $this->rows_per_page;
		$config['uri_segment'] = ($has_filters) ? 5 : 4;
		$config['num_links'] = '10';
		
		$CI->load->library('pagination');
		$CI->pagination->initialize($config);
		
		return true; 
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
    function Action ($name, $link) {
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
    function TableHead () {
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
    			$actions .= '<input type="button" class="action_button" rel="' . site_url($action['link']) . '" name="action_' . $i . '" value="' . $action['name'] . '" />&nbsp;';
    			$i++;
    		}
    	}
    	
    	if ($this->filters == true) {
    		$output .= '<div class="dataset_actions">' . $actions . '</div><div class="apply_filters"><input type="submit" name="" value="Filter Dataset" />&nbsp;&nbsp;<input id="reset_filters" type="reset" name="" value="Clear Filters" />&nbsp;&nbsp;<input id="dataset_export_button" type="button" name="" value="Export" /></div>';
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
						$output .= '<input type="text" class="date_start datepick" name="' . $column['filter_name'] . '_start" value="' . $value . '" />';
						
						$value = (isset($this->filter_values[$column['filter_name'] . '_end'])) ? $this->filter_values[$column['filter_name'] . '_end'] : '';
						$output .= '<input type="text" class="date_end datepick" name="' . $column['filter_name'] . '_end" value="' . $value . '" />';
					}
					elseif ($column['type'] == 'select') {
						$output .= '<select name="' . $column['filter_name'] . '"><option value=""></option>';
						
						while (list($value,$name) = each($column['options'])) {
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
    function TableClose () {
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