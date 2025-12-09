<template>
	<div id="autocomplete_settings">
		<h2>{{ t('autocomplete_settings', 'Autocomplete visibility') }}</h2>
		<p>{{ t('autocomplete_settings', 'Control your visibility in sharing autocomplete') }}</p>
		<NcCheckboxRadioSwitch
			class="autocomplete-switch-wrapper"
			v-model="optedOut"
			:disabled="loading || saving"
			type="switch"
		>
			{{ t('autocomplete_settings', 'Hide from autocomplete suggestions') }}
		</NcCheckboxRadioSwitch>
		<p class="settings-hint">
			{{ t('autocomplete_settings', 'When enabled, your account will not appear in autocomplete suggestions when other users share files or folders. Users can still share with you by typing your exact username.') }}
		</p>
		
		<p v-if="saving" class="autocomplete-hint">
			{{ t('autocomplete_settings', 'Saving...') }}
		</p>
		
		<NcNoteCard v-if="error" type="error">
			{{ error }}
		</NcNoteCard>
	</div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'
import { NcSettingsSection, NcCheckboxRadioSwitch, NcNoteCard } from '@nextcloud/vue'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'AutocompleteSettings',
	components: {
		NcSettingsSection,
		NcCheckboxRadioSwitch,
		NcNoteCard,
	},
	setup() {
		const optedOut = ref(false)
		const loading = ref(true)
		const saving = ref(false)
		const error = ref('')
		const isInitialLoad = ref(true)

		const loadUserPreference = () => {
			loading.value = true
			error.value = ''
			
			try {
				const initialValue = loadState('autocomplete_settings', 'exclude_user_from_autocomplete')
				console.log('Loaded initial value:', initialValue, typeof initialValue)
				
				// Handle various possible formats
				optedOut.value = initialValue === 'true' || initialValue === true || initialValue === '1' || initialValue === 1
				console.log('Set optedOut to:', optedOut.value)
			} catch (err) {
				console.error('Failed to load preference:', err)
				error.value = t('autocomplete_settings', 'Failed to load preference')
				optedOut.value = false
			} finally {
				loading.value = false
				// Allow watch to trigger after initial load completes
				setTimeout(() => {
					isInitialLoad.value = false
				}, 100)
			}
		}

		const savePreference = async (newValue) => {
			console.log('Saving preference, new value:', newValue)
			
			saving.value = true
			error.value = ''

			try {
				const url = generateUrl('/apps/autocomplete_settings/settings/preference')
				console.log('Posting to:', url)
				
				const response = await axios.post(url, {
					value: newValue ? '1' : '0'
				})
				
				console.log('Response:', response.data)
				
				showSuccess(t('autocomplete_settings', 'Autocomplete preference updated'))
			} catch (err) {
				console.error('Failed to save preference:', err)
				console.error('Error details:', err.response?.data)
				
				error.value = t('autocomplete_settings', 'Failed to save preference. Please try again.')
				showError(t('autocomplete_settings', 'Failed to save autocomplete preference'))
				
				// Revert the toggle on error
				optedOut.value = !newValue
			} finally {
				saving.value = false
			}
		}

		// Watch for changes to optedOut and save to server
		watch(optedOut, (newValue, oldValue) => {
			// Only save if:
			// 1. Not during initial load
			// 2. Not currently loading
			// 3. Value actually changed
			if (!isInitialLoad.value && !loading.value && newValue !== oldValue) {
				console.log('optedOut changed from', oldValue, 'to', newValue)
				savePreference(newValue)
			}
		})

		onMounted(loadUserPreference)

		return {
			optedOut,
			loading,
			saving,
			error,
			t,
		}
	},
}
</script>

<style lang="scss" scoped>
.settings-hint {
	color: var(--color-text-maxcontrast);
	margin-top: 8px;
	margin-bottom: 10px;
	margin-left: 30px;
}
</style>