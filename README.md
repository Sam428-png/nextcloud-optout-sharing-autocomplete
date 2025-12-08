# Nextcloud Opt Out for Share AutoComplete

This app allow users to opt-out autocomplete in share dialog, where some users do not want to be found via autocomplete for sharing. 
When a user has opted out of autocomplete, the full user name must be entered to share or the personal address book must be consulted.

![HelplinksAdminInterface.png](https://github.com/T0mWz/nextcloud-optout-sharing-autocomplete/raw/main/docs/OptOut_for_sharing_autocomplete.png)

### Technically

There is a new flag in the `oc_preferences` table which will be used in the `search` function as filter criterium. 

```
    INSERT INTO oc_preferences (userid, appid, configkey, configvalue) 
    VALUES ('UserID', 'files_sharing', 'exclude_user_from_autocomplete', 'true');
```

### Nextcloud Patch

Where this function is not native supported by nextcloud, a patch will need to be applied.
```
patch -d /var/www/nextcloud/ -p1 --backup --suffix=.bak --force --verbose < docs/exclude_from_autocomplete.patch
```