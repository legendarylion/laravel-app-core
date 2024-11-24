<template>
	<v-container>
		<v-card class="pa-4">
			<v-card-title>
				<h2 class="text-h5">Two-Factor Authentication</h2>
			</v-card-title>
			<v-divider></v-divider>

			<v-card-text>
				<!-- Debug Information -->
				<v-alert v-if="isDev" type="info" variant="tonal" class="mb-4">
					<pre class="text-caption">Status:
					- 2FA Enabled: {{ isTwoFactorEnabled }}
					- 2FA Confirmed: {{ isTwoFactorConfirmed }}
					- Has QR Code: {{ !!qrCode }}
					- Recovery Codes: {{ recoveryCodes.length }}</pre>
				</v-alert>

				<!-- Status Alert -->
				<v-alert :type="alertType" :text="alertText" class="mb-4" density="compact" />

				<!-- QR Code and Confirmation -->
				<div v-if="isTwoFactorEnabled && !isTwoFactorConfirmed">
					<p>Scan this QR code with your authenticator app:</p>
					<v-card class="pa-4 mb-4" variant="outlined">
						<div v-if="qrCode" v-html="sanitizedQrCode" class="d-flex justify-center"></div>
						<div v-else class="d-flex justify-center align-center pa-4">
							<v-progress-circular indeterminate />
						</div>
					</v-card>

					<v-form @submit.prevent="confirmTwoFactorAuth" ref="confirmForm">
						<!-- Error Alert -->
						<v-alert v-if="showError" type="error" class="mb-4" density="compact" closable
							@click:close="hideError">
							{{ errorMessage }}
						</v-alert>

						<v-text-field v-model="confirmationCode" label="Enter Code from Authenticator App"
							placeholder="123456" density="comfortable" variant="outlined" :error="showError"
							:error-messages="showError ? errorMessage : undefined"
							:rules="[v => !!v || 'Code is required', v => /^\d{6}$/.test(v) || 'Must be 6 digits']"
							maxlength="6" @input="handleCodeInput" @focus="hideError" />

						<v-btn type="submit" color="primary" class="mt-3" :loading="isConfirming"
							:disabled="!isCodeValid">
							Confirm Two-Factor Authentication
						</v-btn>
					</v-form>
				</div>

				<!-- Recovery Codes -->
				<div v-if="isTwoFactorConfirmed && recoveryCodes.length">
					<p class="mb-3">
						Store these recovery codes in a secure password manager. They can be used to recover access to
						your account if your two-factor authentication device is lost.
					</p>
					<v-card class="pa-4 mb-4" variant="outlined">
						<v-list density="compact">
							<v-list-item v-for="(code, index) in recoveryCodes" :key="index" class="font-monospace">
								{{ code }}
							</v-list-item>
						</v-list>
					</v-card>
					<v-btn color="secondary" class="mt-3" :loading="isRegenerating" @click="regenerateRecoveryCodes">
						Regenerate Recovery Codes
					</v-btn>
				</div>
			</v-card-text>

			<v-divider></v-divider>

			<v-card-actions>
				<v-btn v-if="!isTwoFactorEnabled" color="success" :loading="isEnabling" @click="enableTwoFactorAuth">
					Enable Two-Factor Authentication
				</v-btn>
				<v-btn v-else color="error" :loading="isDisabling" @click="confirmDisable">
					Disable Two-Factor Authentication
				</v-btn>
			</v-card-actions>
		</v-card>

		<!-- Disable Confirmation Dialog -->
		<v-dialog v-model="showConfirmDialog" max-width="400">
			<v-card>
				<v-card-title>Confirm Disable 2FA</v-card-title>
				<v-card-text>
					Are you sure you want to disable two-factor authentication? This will make your account less secure.
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn color="grey" @click="showConfirmDialog = false">Cancel</v-btn>
					<v-btn color="error" @click="disableTwoFactorAuth">Disable</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import DOMPurify from 'dompurify';

// State
const isTwoFactorEnabled = ref(false);
const isTwoFactorConfirmed = ref(false);
const qrCode = ref(null);
const recoveryCodes = ref([]);
const confirmationCode = ref('');
const confirmForm = ref(null);
const errorMessage = ref('');
const showError = ref(false);

// Loading states
const isEnabling = ref(false);
const isConfirming = ref(false);
const isDisabling = ref(false);
const isRegenerating = ref(false);

// Dialog state
const showConfirmDialog = ref(false);

// Computed properties
const isDev = computed(() => {
	return window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
});

const alertType = computed(() => {
	if (isTwoFactorConfirmed.value) return 'success';
	if (isTwoFactorEnabled.value) return 'warning';
	return 'error';
});

const alertText = computed(() => {
	if (isTwoFactorConfirmed.value) return 'Two-factor authentication is enabled and confirmed.';
	if (isTwoFactorEnabled.value) return 'Two-factor authentication is enabled but not yet confirmed. Please scan the QR code and confirm your setup.';
	return 'Two-factor authentication is not enabled.';
});

const sanitizedQrCode = computed(() => {
	return qrCode.value ? DOMPurify.sanitize(qrCode.value) : '';
});

const isCodeValid = computed(() => {
	return confirmationCode.value && confirmationCode.value.length === 6 && /^\d+$/.test(confirmationCode.value);
});

// Methods
const log = (action, data = null, error = null) => {
	if (!isDev.value) return;

	const timestamp = new Date().toISOString();
	const state = {
		timestamp,
		action,
		data,
		currentState: {
			isTwoFactorEnabled: isTwoFactorEnabled.value,
			isTwoFactorConfirmed: isTwoFactorConfirmed.value,
			hasQrCode: !!qrCode.value,
			recoveryCodesCount: recoveryCodes.value.length
		}
	};

	if (error) {
		console.error(`[2FA ${action}]`, state);
	} else {
		console.log(`[2FA ${action}]`, state);
	}
};

const hideError = () => {
	showError.value = false;
	errorMessage.value = '';
};

const handleCodeInput = (event) => {
	const newValue = event.target.value.replace(/\D/g, '');
	confirmationCode.value = newValue;
	if (showError.value) {
		hideError();
	}
};

const fetchTwoFactorInfo = async () => {
	log('Fetching Info - Start');
	try {
		// First check recovery codes - their presence indicates confirmed state
		try {
			const recoveryResponse = await axios.get('/user/two-factor-recovery-codes');
			if (Array.isArray(recoveryResponse.data) && recoveryResponse.data.length > 0) {
				log('Found Recovery Codes', { count: recoveryResponse.data.length });
				recoveryCodes.value = recoveryResponse.data;
				isTwoFactorEnabled.value = true;
				isTwoFactorConfirmed.value = true;
				qrCode.value = null;
				return; // Exit early if we confirmed 2FA is set up
			}
		} catch (error) {
			log('Recovery Codes Check Failed', null, error);
		}

		// If no recovery codes, check if 2FA is enabled but not confirmed
		try {
			const qrResponse = await axios.get('/user/two-factor-qr-code');
			if (qrResponse.data?.svg) {
				log('Found QR Code - 2FA enabled but not confirmed');
				qrCode.value = qrResponse.data.svg;
				isTwoFactorEnabled.value = true;
				isTwoFactorConfirmed.value = false;
				recoveryCodes.value = [];
			} else {
				// No QR code and no recovery codes means 2FA is not enabled
				log('No QR Code - 2FA not enabled');
				isTwoFactorEnabled.value = false;
				isTwoFactorConfirmed.value = false;
				qrCode.value = null;
				recoveryCodes.value = [];
			}
		} catch (error) {
			if (error.response?.status === 404) {
				// 404 on QR code endpoint means 2FA is not enabled
				log('QR Code 404 - 2FA not enabled');
				isTwoFactorEnabled.value = false;
				isTwoFactorConfirmed.value = false;
				qrCode.value = null;
				recoveryCodes.value = [];
			} else {
				log('QR Code Check Failed', null, error);
			}
		}
	} catch (error) {
		log('Fetch Failed', null, error);
		// Don't reset state if we're confirmed
		if (!isTwoFactorConfirmed.value) {
			isTwoFactorEnabled.value = false;
			qrCode.value = null;
			recoveryCodes.value = [];
		}
	}
};

const enableTwoFactorAuth = async () => {
	log('Enable - Start');
	isEnabling.value = true;
	try {
		await axios.post('/user/two-factor-authentication');

		// Clear any existing state
		isTwoFactorEnabled.value = true;
		isTwoFactorConfirmed.value = false;
		qrCode.value = null;
		recoveryCodes.value = [];

		// Wait for QR code generation
		await new Promise(resolve => setTimeout(resolve, 1000));

		// Get QR code
		const qrResponse = await axios.get('/user/two-factor-qr-code');
		if (qrResponse.data?.svg) {
			qrCode.value = qrResponse.data.svg;
		}

		log('Enable - Complete', {
			enabled: isTwoFactorEnabled.value,
			hasQrCode: !!qrCode.value
		});
	} catch (error) {
		log('Enable Failed', null, error);
		errorMessage.value = 'Failed to enable two-factor authentication. Please try again.';
		showError.value = true;
	} finally {
		isEnabling.value = false;
	}
};

const confirmTwoFactorAuth = async () => {
	if (!confirmForm.value?.validate()) return;

	log('Confirm - Start');
	isConfirming.value = true;
	hideError();

	try {
		await axios.post('/user/confirmed-two-factor-authentication', {
			code: confirmationCode.value
		});

		// After successful confirmation, get recovery codes
		const recoveryResponse = await axios.get('/user/two-factor-recovery-codes');
		if (Array.isArray(recoveryResponse.data)) {
			recoveryCodes.value = recoveryResponse.data;
			isTwoFactorConfirmed.value = true;
			qrCode.value = null;
		}

		confirmationCode.value = '';
		log('Confirm - Success');
	} catch (error) {
		log('Confirm Failed', null, error);
		errorMessage.value = 'The provided authentication code was invalid.';
		showError.value = true;
		confirmationCode.value = '';
		confirmForm.value?.reset();
	} finally {
		isConfirming.value = false;
	}
};

const regenerateRecoveryCodes = async () => {
	log('Regenerate - Start');
	isRegenerating.value = true;
	try {
		// First generate new codes
		await axios.post('/user/two-factor-recovery-codes');
		log('Generated new codes');

		// Then fetch the new codes
		const response = await axios.get('/user/two-factor-recovery-codes');
		log('Fetched new codes', { receivedCodes: response.data?.length || 0 });

		if (Array.isArray(response.data)) {
			recoveryCodes.value = response.data;
			log('Regenerate - Success', {
				newCodesCount: recoveryCodes.value.length,
				codes: recoveryCodes.value
			});
		} else {
			throw new Error('Invalid response format for recovery codes');
		}
	} catch (error) {
		log('Regenerate Failed', null, error);
		errorMessage.value = 'Failed to regenerate recovery codes. Please try again.';
		showError.value = true;
	} finally {
		isRegenerating.value = false;
	}
};

const confirmDisable = () => {
	showConfirmDialog.value = true;
};

const disableTwoFactorAuth = async () => {
	log('Disable - Start');
	isDisabling.value = true;
	showConfirmDialog.value = false;

	try {
		await axios.delete('/user/two-factor-authentication');
		isTwoFactorEnabled.value = false;
		isTwoFactorConfirmed.value = false;
		qrCode.value = null;
		recoveryCodes.value = [];
		log('Disable - Success');
	} catch (error) {
		log('Disable Failed', null, error);
		errorMessage.value = 'Failed to disable two-factor authentication. Please try again.';
		showError.value = true;
	} finally {
		isDisabling.value = false;
	}
};

// Initialize
onMounted(() => {
	log('Component Mounted');
	fetchTwoFactorInfo();
});
</script>

<style scoped>
.font-monospace {
	font-family: monospace;
}
</style>