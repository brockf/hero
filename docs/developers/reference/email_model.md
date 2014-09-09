# Email Model

Emails are linked to system actions ([hooks](/docs/developers/reference/app_hooks_library.md)) and are automatically sent to members, administrators, or other email address.  For more information on configuring emails, [click here](/docs/configuration/emails.md).

This model gives you direct to the system emails and related methods.

Emails are stored as template files so that they can be parsed as Smarty templates.

This model also includes the `mail_queue()` method which is linked to the cronjob.  If mail is in the queue, it automatically mails out X mails every 5 minutes until the mail queue is clear.

## Initialization

```
$this->load->model('emails/email_model');
// methods available at $this->email_model->x();
```

## Method Reference

## `void mail_queue ()`

Processes the mail queue.  If the mail queue is not empty, it will send a number of emails (configurable at *Configuration > Settings*) every 5 minutes until the mail queue is clear.

## `void update_layout (string $html)`

Update the global email layout.  This may not be relevant depending on how you have setup the specific email templates.

## `int new_email (string $hook [, array $parameters = array() [, array $to = array() [, array $bcc = array() ]]], string $subject , string $body , boolean $is_html)`

Create a new email in the system attached to a hook.

## `void update_email (int $email_id , string $hook [, array $parameters = array() [, array $to = array() [, array $bcc = array() , string $subject , string $body , boolean $is_html]]])`

Update an existing email.

## `void delete_email (int $email_id)`

Delete an existing email.

## `array get_email (int $email_id)`

Return the data for a particular email, in the same format as `get_emails()`.

## `void get_emails ([array $filters = array()])`

Return an email or emails based on optional filters.  Only active emails will be returned.

Possible Filters: 

* int *id* - The email ID
* string *hook* - The hook the emails are bound to

Each email has the following data:

* *id*
* *hook* 
* *parameters* - Array of parameters
* *parameters_string* - Comma-separated list of parameters
* *subject* - The subject of the email
* *subject_template* - The filename for the subject template
* *body_template* - The filename for the body template
* *recipients* - Array of recipients
* *bccs* - Array of BCC's
* *is_html* - Set to TRUE if this is an HTML email
* *other_recipients* - Comma-separated list of non-admin and non-member recipients
* *other_bccs* - Comma-separated list of non-admin and non-member BCCs
