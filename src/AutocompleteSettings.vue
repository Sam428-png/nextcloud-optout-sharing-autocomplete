<template>
	<NcSettingsSection
		:name="t('autocomplete_settings', 'Autocomplete visibility')"
		:description="t('autocomplete_settings', 'Control your visibility in autocomplete suggestions')">
		<NcCheckboxRadioSwitch
			:modelValue="optedOut"
			:loading="saving"
			:description="t('autocomplete_settings', 'When enabled, your account does not show up in suggestions when others type part of your name, for example in the share dialog or when mentioning someone in a comment. Typing your exact username, full name or email address still finds you.')"
			type="switch"
			@update:modelValue="savePreference">
			{{ t('autocomplete_settings', 'Hide from autocomplete suggestions') }}
		</NcCheckboxRadioSwitch>

		<NcNoteCard v-if="error" type="error" :text="error" />
	</NcSettingsSection>
</template>

<script setup>
import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import { ref } from 'vue'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'

const optedOut = ref(loadState('autocomplete_settings', 'exclude_user_from_autocomplete', false) === true)
const saving = ref(false)
const error = ref('')

/**
 * Store the new preference, and put the switch back if that fails.
 *
 * @param {boolean} newValue whether the user is hidden from autocomplete
 */
async function savePreference(newValue) {
	// The switch is not disabled while saving, so it keeps keyboard focus
	if (saving.value) {
		return
	}

	const previousValue = optedOut.value
	optedOut.value = newValue
	saving.value = true
	error.value = ''

	try {
		await axios.post(generateUrl('/apps/autocomplete_settings/settings/preference'), {
			value: newValue,
		})
		showSuccess(t('autocomplete_settings', 'Autocomplete preference updated'))
	} catch {
		error.value = t('autocomplete_settings', 'Failed to save preference. Please try again.')
		showError(t('autocomplete_settings', 'Failed to save autocomplete preference'))
		optedOut.value = previousValue
	} finally {
		saving.value = false
	}
}
</script>
