# Login Model

Record and retrieve records of member logins.

## Initialization

```
$this->load->model('users/login_model');
```

## Method Reference

## `int new_login (int $user_id)`

Record a login for a user.  Their IP address and browser information will automatically be retrieved, so it is assumed that the login record belongs to the person accessing the website at this time.

## `array get_logins ( [array $filters = array()])`

Retrieve login records based on optional filters.

Possible Filters: 

* int *id* - Specify login record ID
* int *user_id* - Filter by user ID
* string *username* - Filter by username
* int *group_id* - Filter by usergroup ID
* string *ip* - Filter by IP address search (e.g., "125.34.")
* string *browser* - Filter by browser search (e.g., "mozilla")
* date *start_date* - Only retrieve records after or including this date (requires $filters['end_date'])
* date *end_date* - Only retrieve records before or including this date (requires $filters['start_date'])
* string *sort* - Field to sort by
* string *sort_dir* - ASC or DESC
* int *limit* - How many records to retrieve
* int *offset* - Start records retrieval at this record

Each login record is an array with the following data:

* *id*
* *user_id*
* *date*
* *ip*
* *browser*
* *username*
* *email*
* *usergroups*