# Forms

Forms provide the basic functionality of allowing your websites visitors to submit content to a database of responses, and possibly to an email address.  Forms are easily built by adding custom fields to an empty form, and specifying basic configuration details like the form *name* and the *email to send form results to*.

> Developers: This is the same custom field engine that powers all other custom fields in Hero.  If you require a fieldtype for your form that is not already in the system, [create a new custom fieldtype](/docs/developers/forms.md).

## Sending Form Results in an Email

When configuring forms, you have the option to specify an email address for results to be emailed to at the time of submission.  All form field responses will be automatically compiled into an email.  Furthermore, submission date and (potential) membership information will also be prepended to the email so that you have as much data as you require.

## Form Configuration

Each form is configured with the following basic options.

* *Title*
* *URL*
* *Introduction*
* *Access Requires Membership to Group* - Which member groups should be allowed to view this blog?
* *Email Results to* - Email results of form submissions to an email. (Optional)
* *Submission Button* - The text for the submission button.
* *Redirect Link* - The local URL to redirect submissions to.
* *Output Template* - The theme template to use to output the form.

## Displaying Forms

By default, each Hero includes a template file which can linked to your form (via the configuration above) to display this form.  However, if you are a designer, you can easily duplicate or modify this template to customize the way your form looks and behaves.