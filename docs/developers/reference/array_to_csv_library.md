# Array to CSV Library

When given an array of data (even one that is multi-dimensional), this library will return a CSV-formatted string.

Example input:

```
$data = array(
			[0] => array(
		      		'name' => 'Los Angeles',
					'country' => 'United States',
					'people' => array(
									'Bill',
									'Mary',
									'Joe'
								),
					'population' => array(
										'downtown' => '11.2',
										'total' => '14.5'
									),
					'state' => 'CA'
				),
			[1] => array(
		      		'name' => 'Dallas',
					'country' => 'United States',
					'people' => array(
									'Tom',
									'Dick',
									'Harry'
								),
					'population' => array(
										'downtown' => '6.6',
										'total' => '9.1'
									),
					'state' => 'TX'
				)
		);
		
$this->load->library('array_to_csv')
$this->array_to_csv->input($data);
$csv = $this->array_to_csv->output();
```

Output:

```
name,country,people,population_downtown,population_total,state
Los Angeles,United States,"Bill,Mary,Joe",11.2,14.5,CA
Dallas,United States,"Tom,Dick,Harry",6.6,9.1,TX
```

## Initialization

```
$this->load->library('array_to_csv');
```

## Method Reference

## `void input (array $array)`

Pass a (multi-dimensional) array of data to the library.

## `string output ()`

Return the CSV-formatted string of the data passed in `input()`.