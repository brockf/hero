# Global Variables

Hero makes available certain *global variables* across all of your templates.

[tag]{$setting.setting_name}[/tag]

Substitute the name of any setting (seen at Configuration > Settings) for "setting_name".

```
<head>
	<title>{$setting.site_name}</title>
</head>
```

[tag]{$logged_in}[/tag]

Set to TRUE if the user is logged in, FALSE if not.

```
{if $logged_in}
<a href="{url path="user/"}">Account Manager</a>
{else}
<a href="{url path="user/login"}">Please login now</a>
{/if}
```

[tag]{$current_url}[/tag]

Exports the URL of the current page.

```
You are currently at page: {$current_url}
```

[tag]{$uri_segment.1}, {$uri_segment.2}, ...[/tag]

Access each segment of the URI via your Smarty templates.  Segments are taken from the URL like `http://www.example.com/segment1/segment2/segment3/...`.

```
{if $uri_segment.1 == 'store'}
You are in the store!
{/if}
```

[tag]{$member.data}[/tag]

If a user is logged in, all member data is available in the `{$member}` array and available globally.  [All member variables are documented here](/docs/designers/reference/members.md).

Example:

```
{if $logged_in}
Name: {$member.first_name} {$member.last_name}
Username: {$member.username}
Email: {$member.email}
Last Login Time: {$member.last_login}
Signup Date: {$member.signup_date}

{if $member.is_admin}
You are an administrator
{/if}
```

You can also access custom member fields.

For example, if you have a member data field called "business":

```
You work for {$member.business}.
```