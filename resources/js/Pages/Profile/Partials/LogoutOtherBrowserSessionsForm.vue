<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
	password: '',
});

const confirmingLogout = ref(false);

function confirmLogout() {
	confirmingLogout.value = true;
}

function logoutOtherBrowserSessions() {
	form.post(route('other-browser-sessions.destroy'), {
		preserveScroll: true,
		onSuccess: () => {
			form.reset();
			confirmingLogout.value = false;
		},
	});
}
</script>

<template>
	<v-card-text>
		<h2 class="text-h6 font-weight-bold mb-4">Logout Other Browser Sessions</h2>
		<p class="mb-4">
			Manage and log out of your active sessions on other browsers and devices.
		</p>

		<v-btn color="primary" outlined @click="confirmLogout">
			Log Out Other Browser Sessions
		</v-btn>

		<!-- Confirmation Dialog -->
		<v-dialog v-model="confirmingLogout" persistent max-width="500">
			<v-card>
				<v-card-title>
					Confirm Logout
				</v-card-title>
				<v-card-text>
					Please enter your password to confirm that you would like to log out of your other browser sessions.
					<v-text-field v-model="form.password" label="Password" type="password" outlined dark class="mt-4"
						:error-messages="form.errors.password" :disabled="form.processing" />
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn text @click="confirmingLogout = false">Cancel</v-btn>
					<v-btn :loading="form.processing" color="primary" @click="logoutOtherBrowserSessions">
						Log Out
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</v-card-text>
</template>
