# Debug/Profile Mode

Application profiling is an effective way of analyzing the system resource usage of your application, the database queries made, environmental information, and other helpful information in debugging and scaling your application.

Hero provides a simple tool for application profile.  When enabled, each displayed web page of your application will have the following details appended in a table:

* Execution time (benchmarked at various system points)
* GET data
* POST data
* Memory usage
* Class/method being called
* Database queries
* HTTP headers
* Configuration variables

## Enabling Debug/Profile Mode

The Debug/Profile mode can be enabled by [setting $config['debug_profile'] to TRUE in your config.php file](/docs/configuration/advanced.md).