<template>
    <v-dialog v-model="confirmingPassword" persistent max-width="500">
        <v-card>
            <v-card-title class="text-h5">
                Confirm Password
            </v-card-title>

            <v-card-text>
                <p class="text-body-1 mb-4">
                    For your security, please confirm your password to continue.
                </p>

                <v-form @submit.prevent="confirmPassword" ref="form">
                    <v-text-field v-model="form.password" label="Password" :error-messages="form.errors.password"
                        :loading="form.processing" :disabled="form.processing"
                        :type="showPassword ? 'text' : 'password'" variant="outlined" density="comfortable"
                        :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                        @click:append-inner="showPassword = !showPassword" required autocomplete="current-password"
                        @keyup.enter="confirmPassword" />
                </v-form>
            </v-card-text>

            <v-card-actions>
                <v-spacer />

                <v-btn variant="text" color="default" :disabled="form.processing" @click="closeModal">
                    Cancel
                </v-btn>

                <v-btn color="primary" :loading="form.processing" @click="confirmPassword">
                    Confirm
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['close'])

const confirmingPassword = ref(false)
const showPassword = ref(false)
const form = useForm({ password: '' })

watch(() => props.show, (show) => {
    confirmingPassword.value = show

    if (show) {
        setTimeout(() => {
            form.password = ''
            form.clearErrors()
        }, 100)
    }
})

watch(confirmingPassword, (value) => {
    if (!value) {
        emit('close')
    }
})

const closeModal = () => {
    confirmingPassword.value = false
}

const confirmPassword = () => {
    form.post(route('password.confirm'), {
        preserveScroll: true,
        onFinish: () => {
            if (!form.hasErrors()) {
                closeModal()
            }
        },
    })
}
</script>