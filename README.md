# Nextcloud Opt Out for Share AutoComplete

This app allow users to opt-out of autocomplete, where some users do not want to be found via autocomplete suggestions.
When a user has opted out of autocomplete, they no longer show up when other users type part of their name in the share dialog or when mentioning someone in a comment.
The full user name (or exact display name or email address) must be entered to share, or the personal address book must be consulted.

![HelplinksAdminInterface.png](https://github.com/T0mWz/nextcloud-optout-sharing-autocomplete/raw/main/docs/OptOut_for_sharing_autocomplete.png)

Supported Nextcloud versions: 33 – 35.

### Technically

The preference is stored as a user setting:

```
sudo -u www-data php occ user:setting <UserID> files_sharing exclude_user_from_autocomplete true
```

The app registers a collaborator search plugin (see `appinfo/info.xml`) that runs after the Nextcloud user search
and removes opted-out users from the partial matches. Exact matches are kept.
This applies to every feature that uses the Nextcloud collaborator search, such as the share dialog and @mentions in comments.

### Upgrading from 1.0.x

Version 1.0.x needed a patch to `apps/files_sharing`. That is no longer needed, the app now filters the results itself.
An existing patch does no harm and disappears with the next Nextcloud upgrade.