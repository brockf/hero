# Dataset Library

Display a formatted table in the control panel that can be filtered by any of the columns, is automatically paginated, has multirow action buttons, and is automatically available to be exported as a CSV file.

Example usage in a control panel controller:

```
$this->load->library('dataset');
		
$columns = array(
				array(
					'name' => 'ID #',
					'type' => 'id',
					'width' => '5%',
					'filter' => 'text'
					),
				array(
					'name' => 'Title',
					'width' => '55%',
					'filter' => 'title',
					'type' => 'text'
					),
				array(
					'name' => 'Content Type',
					'width' => '20%',
					'filter' => 'type',
					'type' => 'select',
					'options' => array('Type1','Type2','FakeData')
					),
				array(
					'name' => '',
					'width' => '20%'
					)
			);
				
$this->dataset->columns($columns);
$this->dataset->datasource('rss_model','get_feeds');
$this->dataset->base_url(site_url('admincp/rss'));

// initialize the dataset
$this->dataset->initialize();

// add actions
$this->dataset->action('Delete','admincp/rss/delete');

$this->load->view('rss_feeds');
```

Then, in the loaded view file:

```
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><a href="<?=site_url('admincp/rss/edit/' . $row['id']);?>"><?=$row['title'];?></a></td>
			<td><?=$row['type_name'];?></td>
			<td class="options"><a href="<?=site_url('admincp/rss/edit/' . $row['id']);?>">edit</a> <a href="<?=$row['url'];?>">view</a></td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="5">No RSS feeds available.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
```

## Initialization

```
$this->load->library('dataset');
// methods call:
$this->dataset->datasource('content_model','get_contents');
```

## Method Reference

## `boolean datasource (string $data_model , string $data_function [, array $data_filters = 'none'])`

Sets the data source with a model, method, and any default parameters to pass to this method.  If the model is not loaded, it will be loaded automatically (thus, it should be in "module/model" format).

The `$data_filters` array is a way to pass certain filters to the data method (via the first parameter, an array of filters).  These will be sent regardless of the filters set in the dataset.

## `void columns (array $columns)`

Specify, in a multi-dimensional array, a series of columns in the dataset.

Each column should have the following key:

* *name*

Each column can optionally have the following keys:

* *sort_column* - The "sort" parameter to send to the datasource method if asked to source by this column.
* *width* - The width of this column (e.g., "20%")
* *type* - The type of filter for this column, if filterable (either "text", "id", "date", or "select")
* *filter* - The name of the filter for this column, if filterable (e.g., "title").  Should correspond with the datasource method's `$filters` specifications.
* *options* - If type is "select", you must supply an array of options in the format of value => name.
* *field_start_date* - If type is "date", you must supply a filter for the datasource method corresponding with the start date.
* *field_end_date* - If type is "date", you must supply a filter for the datasource method corresponding with the end date.

## `boolean initialize (boolean $paginate_now = TRUE)`

Initialize the dataset after setting the columns, datasource, and other configurations.  If you want to hold off on pagination (likely because you want to pass the total rows manually to reduce database load), you can set `$paginate_now` to FALSE.

## `void initialize_pagination ()`

Create the pagination HTML if you postponed pagination at `initialize()`.

## `string table_head ()`

Retrieve the generated HTML to be placed before the table rows are outputted.

```
<?=$this->dataset->table_head();?>
```

## `string table_close ()`

Retrieve the generated HTML to be placed after the table rows are outputted.

```
<?=$this->dataset->table_close();?>
```

## `array get_filter_array ()`

Get an array of key => value filters from the current URL.

## `string get_encoded_filters ()`

Encode the filter array into ASCIIHex format so that it can be used in a URL.

## `int get_limit ()`

Get the current per-page limit either from the URL or `$this->rows_per_page`.

## `int get_offset ()`

Get the current page offset from the URL, or return "0" for the beginning.

## `void get_unlimited_parameters ()`

Get an array of all parameters for the datasource method without any limits or offsets (used to automatically retrieve the total rows in the dataset if this isn't hardset with `total_rows()`).

## `string get_pagination ()`

Retrieve the pagination HTML.

## `boolean has_filters ()`

Does this dataset have active filtering?

## `boolean total_rows (int $total_rows)`

Set `$this->total_rows`.

## `bool action (string $name , string $link)`

Add an action to `$this->actions`.

## `boolean rows_per_page (int $rows_per_page)`

Set the `$this->rows_per_page` configuration.

## `boolean base_url (string $base_url)`

Set the `$this->base_url` configuration.
