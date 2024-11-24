<template>
    <v-container>
        <v-row justify="center" align="center">
            <v-col cols="12" sm="8" md="6" lg="4">
                <v-card class="elevation-12">
                    <v-toolbar color="primary" dark flat>
                        <v-toolbar-title>Two Factor Authentication</v-toolbar-title>
                    </v-toolbar>

                    <v-card-text>
                        <div v-if="!useRecoveryCode">
                            <p class="text-subtitle-1 mb-4">
                                Please confirm access to your account by entering the authentication code provided by
                                your authenticator application.
                            </p>

                            <form @submit.prevent="submit">
                                <v-text-field v-model="form.code" label="Authentication Code"
                                    placeholder="Enter your 6-digit code" :error-messages="form.errors.code"
                                    maxlength="6" autocomplete="one-time-code" class="mb-4" variant="outlined"
                                    density="comfortable" @input="form.errors.code = ''"></v-text-field>

                                <v-btn type="submit" color="primary" block class="mt-4" :loading="form.processing">
                                    Verify
                                </v-btn>
                            </form>
                        </div>

                        <div v-else>
                            <p class="text-subtitle-1 mb-4">
                                Please confirm access to your account by entering one of your emergency recovery codes.
                            </p>

                            <form @submit.prevent="submitRecoveryCode">
                                <v-text-field v-model="form.recovery_code" label="Recovery Code"
                                    placeholder="Enter recovery code" :error-messages="form.errors.recovery_code"
                                    autocomplete="one-time-code" class="mb-4" variant="outlined" density="comfortable"
                                    @input="form.errors.recovery_code = ''"></v-text-field>

                                <v-btn type="submit" color="primary" block class="mt-4" :loading="form.processing">
                                    Use Recovery Code
                                </v-btn>
                            </form>
                        </div>

                        <v-btn text block class="mt-4" @click="useRecoveryCode = !useRecoveryCode">
                            {{ useRecoveryCode ? 'Use authentication code' : 'Use recovery code instead' }}
                        </v-btn>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const useRecoveryCode = ref(false);
const form = useForm({
    code: '',
    recovery_code: ''
});

const submit = () => {
    form.post(route('two-factor.login'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.code) {
                form.code = '';
            }
        }
    });
};

const submitRecoveryCode = () => {
    form.post(route('two-factor.login'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.recovery_code) {
                form.recovery_code = '';
            }
        }
    });
};
</script>