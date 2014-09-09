# Clean String Helper

Remove all characters in a string that are neither alphanumeric nor underscores.  Spaces are converted to underscores.  All characters are forced to lowercase.

## Reference

## `string clean_string (string $string)`

```
$string = 'My test %string%';
$this->load->helper('clean_string');
echo clean_string($string);
```

Output:

```
my_test_string
```