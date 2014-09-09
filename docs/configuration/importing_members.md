# Importing Members

If you have an existing members database, you can import these members automatically into Hero with the integrated CSV import function in the control panel.

This function is found at *Members > Import Members* in the control panel.

## Import Source

Members can be imported from a CSV (Comma Separated Value) file, formatted like so:

```
Paul,Smith,paul@smithfamily.com,grgt498xt,Canada,2009-10-05
Heather,Jones,heather@gmail.com,59e0grgj,USA,2006-04-29
...
```

You can list your member's email, first name, last name, password, and other [custom member data fields](/docs/configuration/custom_fields.md) (in the example above, Residence Country and signup date) in any order in this file.  What is important is that you have each member's data on a newline and each field separated by a comma, as is the format of all CSV files.

Each member record must have an email address, last name, and first name in the CSV file.

If a username is not specified, the email address will be used.  If a password is not specified, a random 8-character string will be used.

## Importing

After you upload your CSV file, you will be able to label each field column in the CSV file with the corresponding member data field (e.g., email, last name, or any [custom member data field](/docs/configuration/custom_fields.md) you are using).

Then, simply press "Import" to complete the import of these users with the field assignments selected.

## Automatic Registration Email

When accounts are created, all standard triggers will be tripped and so, if you have an email setup for the <b>member_register</b> hook, a registration email will be sent to the user.  If you would prefer not to email your users upon import, you should delete this email temporarily.

## Account Creation Errors

After importing users, any failed imports will be listed along with the cause of the import problem associated with that account.  You can then fix those records and re-import.