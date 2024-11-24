<script setup>
import { ref } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';

const user = usePage().props.auth.user;

// Manage form state
const form = useForm({
	processing: false,
});

// State variables for 2FA
const showingQrCode = ref(false);
const showingRecoveryCodes = ref(false);

function enableTwoFactorAuthentication() {
	form.post(route('two-factor.enable'), {
		preserveScroll: true,
		onSuccess: () => {
			showingQrCode.value = true;
			showingRecoveryCodes.value = true;
		},
	});
}

function disableTwoFactorAuthentication() {
	form.delete(route('two-factor.disable'), {
		preserveScroll: true,
		onSuccess: () => {
			showingQrCode.value = false;
			showingRecoveryCodes.value = false;
		},
	});
}

function regenerateRecoveryCodes() {
	form.post(route('two-factor.recovery-codes'), {
		preserveScroll: true,
		onSuccess: () => {
			showingRecoveryCodes.value = true;
		},
	});
}
</script>

<template>
	<v-card-text>
		<h2 class="text-h6 font-weight-bold mb-4">Two-Factor Authentication</h2>
		<p class="mb-4">
			Add an extra layer of security to your account by enabling two-factor authentication.
		</p>

		<!-- Enable/Disable Button -->
		<div v-if="!user.two_factor_enabled">
			<v-btn :loading="form.processing" color="primary" @click="enableTwoFactorAuthentication">
				Enable Two-Factor Authentication
			</v-btn>
		</div>
		<div v-else>
			<v-btn :loading="form.processing" color="red" outlined @click="disableTwoFactorAuthentication">
				Disable Two-Factor Authentication
			</v-btn>
		</div>

		<!-- QR Code Section -->
		<div v-if="user.two_factor_enabled && showingQrCode" class="mt-6">
			<h3 class="text-h6 mb-2">QR Code</h3>
			<p>Scan the following QR code using your authentication app.</p>
			<div class="text-center">
				<img :src="user.two_factor_qr_code_url" alt="Two-Factor QR Code" class="mt-4" />
			</div>
		</div>

		<!-- Recovery Codes Section -->
		<div v-if="user.two_factor_enabled && showingRecoveryCodes" class="mt-6">
			<h3 class="text-h6 mb-2">Recovery Codes</h3>
			<p>
				Store these recovery codes in a secure location. They can be used to recover access to your account if
				you lose your authentication device.
			</p>
			<v-card outlined dark class="pa-4 mt-4">
				<v-list dense>
					<v-list-item v-for="code in user.two_factor_recovery_codes" :key="code">
						<v-list-item-content>{{ code }}</v-list-item-content>
					</v-list-item>
				</v-list>
			</v-card>
			<v-btn :loading="form.processing" color="primary" class="mt-4" outlined @click="regenerateRecoveryCodes">
				Regenerate Recovery Codes
			</v-btn>
		</div>
	</v-card-text>
</template>
