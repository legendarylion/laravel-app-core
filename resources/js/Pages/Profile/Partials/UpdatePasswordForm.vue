<!-- resources/js/Pages/Profile/Partials/UpdatePasswordForm.vue -->
<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const form = useForm({
	current_password: '',
	password: '',
	password_confirmation: '',
});

const updatePassword = () => {
	form.put(route('user-password.update'), {
		errorBag: 'updatePassword',
		preserveScroll: true,
		onSuccess: () => form.reset(),
	});
};
</script>

<template>
	<v-card>
		<v-card-title class="text-h6">
			Update Password
		</v-card-title>

		<v-card-text>
			<div class="text-subtitle-1 mb-4">
				Ensure your account is using a long, random password to stay secure.
			</div>

			<v-form @submit.prevent="updatePassword">
				<v-text-field v-model="form.current_password" :type="showCurrentPassword ? 'text' : 'password'"
					label="Current Password" :error-messages="form.errors.current_password" required variant="outlined"
					prepend-inner-icon="mdi-lock" :append-inner-icon="showCurrentPassword ? 'mdi-eye' : 'mdi-eye-off'"
					@click:append-inner="showCurrentPassword = !showCurrentPassword" class="mb-4"></v-text-field>

				<v-text-field v-model="form.password" :type="showNewPassword ? 'text' : 'password'" label="New Password"
					:error-messages="form.errors.password" required variant="outlined" prepend-inner-icon="mdi-lock"
					:append-inner-icon="showNewPassword ? 'mdi-eye' : 'mdi-eye-off'"
					@click:append-inner="showNewPassword = !showNewPassword" class="mb-4"></v-text-field>

				<v-text-field v-model="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'"
					label="Confirm Password" required variant="outlined" prepend-inner-icon="mdi-lock-check"
					:append-inner-icon="showConfirmPassword ? 'mdi-eye' : 'mdi-eye-off'"
					@click:append-inner="showConfirmPassword = !showConfirmPassword" class="mb-4"></v-text-field>

				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn type="submit" color="primary" :loading="form.processing" :disabled="form.processing">
						Save
					</v-btn>
				</v-card-actions>
			</v-form>
		</v-card-text>
	</v-card>
</template>