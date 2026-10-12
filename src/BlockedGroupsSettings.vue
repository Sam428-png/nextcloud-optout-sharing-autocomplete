<template>
	<NcSettingsSection
		:name="t('autocomplete_settings', 'Blocked groups')"
		:description="t('autocomplete_settings', 'Nobody can share files or folders with these groups, and they do not show up in suggestions, for example in the share dialog. Use this for large groups that are only meant for configuration, such as enabling an app for everyone. Existing shares keep working, but cannot get more permissions. Calendars, address books and teams that contain such a group are not covered.')">
		<NcSettingsSelectGroup
			:modelValue="blockedGroups"
			:label="t('autocomplete_settings', 'Groups that nobody can share with')"
			@update:modelValue="saveBlockedGroups" />
	</NcSettingsSection>
</template>

<script setup>
import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import { ref } from 'vue'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'
import NcSettingsSelectGroup from '@nextcloud/vue/components/NcSettingsSelectGroup'

const blockedGroups = ref(loadState('autocomplete_settings', 'blocked_share_groups', []))

// Saves run one after the other, so the server always ends up with the last selection
let savedGroups = blockedGroups.value
let queue = Promise.resolve()
let latestSave = 0

/**
 * Store the new list of blocked groups. If that fails, show the list that is stored on the server.
 *
 * @param {string[]} groups the ids of the blocked groups
 */
function saveBlockedGroups(groups) {
	blockedGroups.value = groups
	const save = ++latestSave

	queue = queue.then(async () => {
		try {
			const { data } = await axios.put(generateUrl('/apps/autocomplete_settings/settings/blocked-groups'), { groups })
			savedGroups = data.groups
			if (save === latestSave) {
				blockedGroups.value = data.groups
				showSuccess(t('autocomplete_settings', 'Blocked groups saved'))
			}
		} catch {
			if (save === latestSave) {
				blockedGroups.value = savedGroups
			}
			showError(t('autocomplete_settings', 'Failed to save the blocked groups'))
		}
	})
}
</script>
