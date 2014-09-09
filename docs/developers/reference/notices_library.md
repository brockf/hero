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

## `boolean SetError (string $message)`

Set an error to be displayed upon the next page load.

## `array GetErrors (boolean $clear)`

Return an HTML formatted string of all errors (in your view, most likely).

## `boolean SetNotice (string $message)`

Set a notice to be displayed upon the next page load.

## `array GetNotices (boolean $clear)`

Return an HTML formatted string of all notices (in your view, most likely).