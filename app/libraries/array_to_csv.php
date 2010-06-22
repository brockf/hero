<?php

/* Array to CSV
*
* Converts an array (including multi-dimensional arrays) into 
* a CSV formatted string.
*/

class Array_to_csv {
	var $data;
	var $headers;
	var $parsed_data;
	
	public function input ($array = array()) {
		$this->data = $array;
		$this->headers = array();
		$this->parsed_data = array();
	}
	
	public function output () {
		// get headers based on the item with the most data
		foreach ($this->data as $datum) {
			$headers = $this->get_keys($datum);
			
			if (count($headers) > count($this->headers)) {
				$this->headers = $headers;
			}
		}
		reset($this->data);
		
		// let's parse each row of data for those headers
		foreach ($this->data as $datum) {
			$this->parsed_data[] = $this->get_values($datum);
		}
		
		// process output
		// header line
		$csv = '';
		$csv .= implode(',',$this->headers) . "\n";
		
		foreach ($this->parsed_data as $line) {
			foreach ($this->headers as $header) {
				$value = $line[$header];
				$value = str_replace("\n",' ',$value);
				$csv .= '"' . $value . '",';
			}
			
			$csv = rtrim($csv,',');
			
			$csv .= "\n";
		}
		
		echo $csv;
	}
	
	// gets all possible data from a multi-dimensional array, in one array
	private function get_values ($array = array(), $prefix = '') {
		$values = array();
		
		foreach ($array as $key => $value) {
			if (!is_array($value) and !is_numeric($key) and is_bool($value)) {
				// TRUE or FALSE
				$values[$prefix . $key] = ($value == TRUE) ? '1' : '0';
			}
			elseif (!is_array($value) and !is_numeric($key)) {
				$values[$prefix . $key] = $value;
			}
			elseif (is_array($value) and isset($value[0])) {
				// this is just a data array, like $user['usergroups']
				// we'll take the key name
				$values[$prefix . $key] = implode(',',$value);
			}
			elseif (!is_numeric($key)) {
				$values = array_merge($values,$this->get_values($value,$key . '_'));
			}
		}
		
		return $values;
	}
	
	// gets the name of all keys, including child arrays
	private function get_keys ($array = array(), $prefix = '') {
		$keys = array();
		
		foreach ($array as $key => $value) {
			if (!is_array($value) and !is_numeric($key)) {
				$keys[] = $prefix . $key;
			}
			elseif (is_array($value) and isset($value[0])) {
				// this is just a data array, like $user['usergroups']
				// we'll take the key name
				$keys[] = $prefix . $key;
			}
			elseif (!is_numeric($key)) {
				$keys = array_merge($keys,$this->get_keys($value,$key . '_'));
			}
		}
		
		return $keys;
	}
}