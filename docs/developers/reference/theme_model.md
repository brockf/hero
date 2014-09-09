# Theme Model

Retrieve and, in the future, interact with the themes in your Hero install.

## Initialization

```
$this->load->model('themes/theme_model');
// methods at $this->theme_model->x();
```

## Method Reference

## `array get_themes()`

Retrieve an array of all themes available in your install.

```
$themes = $this->theme_model->get_themes();

echo 'List of themes:<br /><br />';

foreach ($themes as $theme) {
	echo $theme . '<br />';
}
```