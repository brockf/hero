# Time Since Helper

When passed a date, this function will return a more friendly date string like "2 seconds ago", "4 min ago", or "8 hours ago".  If the date is too far back, it will just return FALSE.

## Reference

## `string|boolean time_since (date $date)`

Return a nicely-formatted date, or else FALSE if too old.

```
$this->load->helper('time_since');
$date = '2010-11-10 04:43:32';

$date_string = time_since($date);
if ($date_string !== FALSE) {
	echo 'This happened ' . $date_string;
}
else {
	echo 'This happened on ' . date('M d, Y \@ h:ia', strtotime($date));
}
```