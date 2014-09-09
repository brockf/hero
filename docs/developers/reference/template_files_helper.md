# Template Files Helper

Retrieve an alphabetical array of all template files within the current frontend theme's directory.

## Reference

## `array template_files ()`

Return array of template files in frontend theme's folder (e.g., at `/themes/orchard/` if your theme is "orchard").

```
$this->load->helper('template_files');
$files = template_files();
```