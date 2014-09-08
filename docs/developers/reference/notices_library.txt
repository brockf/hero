# Notices Library

Primarily a control panel library, the Notices library allows you to specify errors and notices which - upon the **next** page load - will be displayed as errors/notices in the standard control panel notice format (default: at the top of the screen in red/green boxes).

This library uses session flashdata to store the notices and errors until the next page load.

## Initialization

If you are using this library in the control panel, it will be initialized automatically.

If you want to use it outside of controllers that extend `Admincp_Controller`, you will need to load it:

```
$this->load->library('notices');
```

## Method Reference

[method]boolean SetError (string $message)[/method]

Set an error to be displayed upon the next page load.

[method]array GetErrors (boolean $clear)[/method]

Return an HTML formatted string of all errors (in your view, most likely).

[method]boolean SetNotice (string $message)[/method]

Set a notice to be displayed upon the next page load.

[method]array GetNotices (boolean $clear)[/method]

Return an HTML formatted string of all notices (in your view, most likely).