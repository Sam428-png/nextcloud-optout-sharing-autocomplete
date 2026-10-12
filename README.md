# Sharing visibility for Nextcloud

This app gives users and admins more control over who shows up when sharing.

**Users can hide from autocomplete.** Some users do not want to be found through autocomplete suggestions.
After opting out in their personal sharing settings, they no longer show up when other users type part of their name
in the share dialog or when mentioning someone in a comment.
They can still be found by typing their exact user name, full name or email address.

**Admins can block groups for sharing.** Some groups are only meant for configuration, such as enabling an app for everyone,
and are far too large to share with. In the admin sharing settings, admins can block such groups.
Nobody can share files or folders with a blocked group, whichever way the share is created, and blocked groups do not show up in sharing suggestions.
Existing shares with a blocked group keep working. They can still be renamed, moved and transferred, but cannot get more permissions.

![HelplinksAdminInterface.png](https://github.com/T0mWz/nextcloud-optout-sharing-autocomplete/raw/main/docs/OptOut_for_sharing_autocomplete.png)

Supported Nextcloud versions: 34.0.2 – 35.

### Technically

The opt-out is stored as a user setting. To set it for a user, delete any value they set themselves first:

```
sudo -u www-data php occ user:setting <UserID> files_sharing exclude_user_from_autocomplete --delete
sudo -u www-data php occ user:setting <UserID> files_sharing exclude_user_from_autocomplete true
```

The blocked groups are stored in the app config:

```
sudo -u www-data php occ config:app:set autocomplete_settings blocked_share_groups --value '["<GroupID>"]' --type array
```

If the value was stored with another type, no group is blocked and an error is logged.
Saving the blocked groups in the admin settings fixes it, or delete it and set it again:

```
sudo -u www-data php occ config:app:delete autocomplete_settings blocked_share_groups
```

The app registers collaborator search plugins (see `appinfo/info.xml`) that run after the Nextcloud user and group search.
They remove opted-out users from the partial matches, keeping exact matches, and remove blocked groups completely.
This applies to every feature that uses the Nextcloud collaborator search, such as the share dialog and @mentions in comments.

Shares with blocked groups are refused through the Nextcloud interaction API (`RestrictInteractionEvent`).
Nextcloud also checks this when it updates existing shares, for example after a rename,
so those updates are allowed as long as they do not add permissions.

### Limits

- The share dialog suggests recipients you shared with before, before you type anything. A blocked group can still show up there,
  but sharing with it is refused.
- Opted-out users can still be found through the contacts menu and the people search in the unified search,
  and in the calendar and address book sharing dialogs.
- Calendars and address books can still be shared with a blocked group, as they are not shared through the files sharing API.
- A blocked group can still be reached through a team, Talk conversation or Deck board that contains it.
  To stop groups from being added to teams, see the `allowed_types` setting of the Teams (circles) app.

### Tests

The tests run against a real Nextcloud, using the official Docker image:

```
npm ci && npm run build
make test nc=35
```

`tests/e2e/e2e.sh` can also run against an existing test server over SSH, see the comment at the top of the script.
GitHub Actions runs the tests on Nextcloud 34 and 35 for every pull request, and weekly against the newest Nextcloud release.

### Upgrading from 1.0.x

Version 1.0.x needed a patch to `apps/files_sharing`. That is no longer needed, the app now filters the results itself.
An existing patch does no harm and disappears with the next Nextcloud upgrade.
