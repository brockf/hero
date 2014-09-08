# Head Assets Library

This library is used to add JavaScript includes, CSS stylesheet links, and other HTML to the `<head>` of the control panel during script execution.

## Initialization

This library is automatically initialized in the `Admincp_controller`.

Example method calls are below.

## Method Reference

[method]void stylesheet (string $file [, boolean $is_branded = TRUE [, string $rel = 'stylesheet' [, string $type = 'text/css']]])[/method]

Add a stylesheet to the `<head>`.  If `$is_branded` is `TRUE`, it will look for the file in the `/branding/` folder and follow the auto-branding logic.

```
$this->head_assets->stylesheet('css/dataset.css');
```

[method]void javascript (string $file [, boolean $is_branded = TRUE [, string $type = 'text/javascript']])[/method]

Add a JavaScript `<script>` include to the `<head>`.  If `$is_branded` is `TRUE`, it will look for the file in the `/branding/` folder and follow the auto-branding logic.

```
$this->head_assets->javascript('js/form.my_module.js');
```

[method]void code (string $code)[/method]

Add miscellaneous code to the `<head>`.

```
$this->head_assets->code('<script type="text/javascript">alert('Why didn't I use an included file?');</script>');

[method]string display ()[/method]

Output the code that has been added to head.  This should be called in the control panel view template.

```
<head>
<?=$this->head_assets->display();
<!-- other code -->
</head>
```



