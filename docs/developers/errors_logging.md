# Errors and Logging

Hero allows you to report errors using standard, formatted error functions.  Additionally, to provide more detail for debugging issues with your application, there is a logging system which will save text-based logs in the `/system/logs/` folder when enabled.

## Error Reporting

Both of the available functions are available globally throughout the application.

## `void show_error( string $error_message )`

Display an error to the browser with the standard error formatting.  Typically, you would want to kill application execution at this time.

Usage:

```
die(show_error('Error: Failed to load item you requested.'));
```

## `void show_404( [string $path] )`

Throw a 404 File not Found error to the browser.  The 404 HTTP Header will automatically be passed, providing this function is called before any output has been sent to the browser.

The optional `$path` can be passed to the function to say which route was not found, exactly.

Usage:

```
die(show_404('my_controller/' . $this->input->get('content')));
```

## Logging

Through the `log_message()` function below, you can log various levels of messages during your application's execution.  In order for these log messages to be stored, the instance must be [configured to have logging enabled](/docs/configuration/advanced.md).

## `void log_message( string $type , string $message )`

Place an entry (`$message`) in the site logs.

Available log levels:

* error
* debug
* info

Usage:

```
if ($some_var == "")
{
    log_message('error', 'Some variable did not contain a value.');
}
else
{
    log_message('debug', 'Some variable was correctly set');
}

log_message('info', 'The purpose of some variable is to provide some value.');
```