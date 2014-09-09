# Array to JSON Helper

Convert an array to JSON format.

## Reference

## `string array_to_json (array $array)`

```
$array = array('status' => 'Success', 'message' => 'You have created the post successfully.');

$this->load->help('array_to_json');
$json = array_to_json($array);

// $json now = { status : "Success", message : "You have created the post successfully." }
```