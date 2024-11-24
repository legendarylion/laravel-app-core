<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
	password: '',
});

const confirmingDeletion = ref(false);

function confirmUserDeletion() {
	confirmingDeletion.value = true;
}

function deleteUser() {
	form.delete(route('current-user.destroy'), {
		preserveScroll: true,
		onSuccess: () => {
			confirmingDeletion.value = false;
		},
	});
}
</script>

<template>
	<v-card-text>
		<h2 class="text-h6 font-weight-bold mb-4">Delete Account</h2>
		<p class="mb-4">
			Once your account is deleted, all of its resources and data will be permanently deleted. Please download any
			data or information that you wish to retain before deleting your account.
		</p>

		<v-btn color="red" outlined @click="confirmUserDeletion">
			Delete Account
		</v-btn>

		<!-- Confirmation Dialog -->
		<v-dialog v-model="confirmingDeletion" persistent max-width="500">
			<v-card>
				<v-card-title>
					Confirm Account Deletion
				</v-card-title>
				<v-card-text>
					Are you sure you want to delete your account? Once your account is deleted, all of its resources and
					data will be permanently deleted.
					<v-text-field v-model="form.password" label="Password" type="password" outlined dark class="mt-4"
						:error-messages="form.errors.password" :disabled="form.processing" />
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn text @click="confirmingDeletion = false">Cancel</v-btn>
					<v-btn :loading="form.processing" color="red" @click="deleteUser">
						Delete Account
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</v-card-text>
</template>
