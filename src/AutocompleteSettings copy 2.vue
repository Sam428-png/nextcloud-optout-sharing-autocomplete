<template>
	<NcSettingsSection :name="t('autocomplete_settings', 'Autocomplete visibility')"
		:description="t('autocomplete_settings', 'Control your visibility in sharing autocomplete')">
		
		<NcCheckboxRadioSwitch :checked="optedOut"
			:disabled="loading || saving"
			type="switch"
			@update:checked="handleToggle">
			{{ t('autocomplete_settings', 'Hide from autocomplete suggestions') }}
		</NcCheckboxRadioSwitch>
		<p class="settings-hint">
			{{ t('autocomplete_settings', 'When enabled, your account will not appear in autocomplete suggestions when other users share files or folders. Users can still share with you by typing your exact username.') }}
		</p>
		<NcNoteCard v-if="success" type="success">
			{{ success }}
		</NcNoteCard>
		<NcNoteCard v-if="error" type="error">
			{{ error }}
		</NcNoteCard>
		<NcNoteCard type="info">
			<p><strong>{{ t('autocomplete_settings', 'How this works:') }}</strong></p>
			<ul>
				<li>{{ t('autocomplete_settings', 'Your username won\'t appear in autocomplete dropdowns') }}</li>
				<li>{{ t('autocomplete_settings', 'Direct sharing by typing your exact username still works') }}</li>
				<li>{{ t('autocomplete_settings', 'Only affects new share operations, not existing shares') }}</li>
			</ul>
		</NcNoteCard>
	</NcSettingsSection>
</template>

<script>
import { ref, onMounted } from 'vue'
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
		const success = ref('')

		const loadUserPreference = async () => {
			loading.value = true
			error.value = ''
			try {
				// Load from initial state
				const initialValue = loadState('autocomplete_settings', 'exclude_user_from_autocomplete')
				console.log('Loaded initial value:', initialValue, 'Type:', typeof initialValue)
				optedOut.value = initialValue === 'true' || initialValue === true || initialValue === '1' || initialValue === 1
				console.log('Set optedOut to:', optedOut.value)
			} catch (err) {
				console.error('Failed to load autocomplete preference:', err)
				error.value = t('autocomplete_settings', 'Failed to load preference')
				// Set default value if loading fails
				optedOut.value = false
			} finally {
				loading.value = false
			}
		}

		const handleToggle = async (newValue) => {
			console.log('Toggle clicked, new value:', newValue)
			
			saving.value = true
			error.value = ''
			success.value = ''
			
			try {
				const url = generateUrl('/apps/autocomplete_settings/settings/preference')
				console.log('Posting to URL:', url, 'with value:', newValue)
				
				const response = await axios.post(url, {
					value: newValue ? '1' : '0', // Send as string for consistency
				})
				
				console.log('Server response:', response.data)
				
				optedOut.value = newValue
				success.value = t('autocomplete_settings', 'Preference saved successfully')
				showSuccess(t('autocomplete_settings', 'Autocomplete preference updated'))
				
				// Clear success message after 3 seconds
				setTimeout(() => {
					success.value = ''
				}, 3000)
			} catch (err) {
				console.error('Failed to save autocomplete preference:', err)
				console.error('Error response:', err.response?.data)
				error.value = t('autocomplete_settings', 'Failed to save preference. Please try again.')
				showError(t('autocomplete_settings', 'Failed to save autocomplete preference'))
				
				// Revert the toggle
				optedOut.value = !newValue
			} finally {
				saving.value = false
			}
		}

		onMounted(() => {
			loadUserPreference()
		})

		return {
			optedOut,
			loading,
			saving,
			error,
			success,
			handleToggle,
			t, // Make sure t is returned
		}
	},
}
</script>

<style lang="scss" scoped>
.settings-hint {
	color: var(--color-text-maxcontrast);
	margin-top: -10px;
	margin-bottom: 10px;
	margin-left: 30px;
}

ul {
	margin-left: 20px;
	margin-top: 8px;
}

ul li {
	margin-bottom: 4px;
}
</style>