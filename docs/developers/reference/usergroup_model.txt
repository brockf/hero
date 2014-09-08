# Usergroup Model

Usergroups are simple categorization tools for members.  They provide little power independently, but can be used by other applications and within Hero with great purpose.  For example, subscriptions promote users to usergroups that can be used to determine a user's access to content.

## Initialization

```
$this->load->model('users/usergroup_model');
// methods at $this->usergroup_model->x();
```

## Method Reference

[method]int new_group (string $name)[/method]

Create a new usergroup.

Arguments:

* `$name` - The usergroup name

[method]boolean update_group (int $group_id , string $name)[/method]

Update an existing usergroup's name.

[method]boolean make_default (int $group_id)[/method]

Make this usergroup the default usergroup - the group that new registrations are automatically placed into.

[method]boolean delete_group (int $group_id)[/method]

Delete a user group.  All users will be removed from this group (though they will obviously not be deleted).

[method]int get_default ()[/method]

Get the ID of the default usergroup.

[method]array get_group (int $group_id)[/method]

Retrieve an array for a usergroup, containing the group's *id* and *name*.

[method]array get_usergroups ([array $filters = array()])[/method]

Retrieve an array of usergroup(s) with their respective *id*s and *name*s.

Possible filters:

* *id* (to retrieve just one group)
