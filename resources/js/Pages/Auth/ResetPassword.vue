<!-- resources/js/Pages/Auth/ForgotPassword.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <v-app>
        <v-main>
            <v-container fluid class="fill-height">
                <v-row justify="center" align="center">
                    <v-col cols="12" sm="8" md="4">
                        <v-card class="elevation-12">
                            <v-toolbar color="primary" flat>
                                <v-toolbar-title>Reset Password</v-toolbar-title>
                            </v-toolbar>

                            <v-card-text class="pa-6">
                                <div class="text-medium-emphasis mb-4">
                                    Forgot your password? No problem. Just let us know your email address and we will
                                    email
                                    you a password reset link that will allow you to choose a new one.
                                </div>

                                <!-- Session Status -->
                                <v-alert v-if="$page.props.status" color="success" class="mb-4" variant="tonal">
                                    {{ $page.props.status }}
                                </v-alert>

                                <v-form @submit.prevent="submit">
                                    <v-text-field v-model="form.email" :error-messages="form.errors.email" label="Email"
                                        type="email" required variant="outlined" prepend-icon="mdi-email"
                                        persistent-placeholder :append-inner-icon="null" class="mb-2" />

                                    <div class="d-flex flex-column gap-4">
                                        <v-btn type="submit" color="primary" block :loading="form.processing"
                                            :disabled="form.processing" elevation="2">
                                            Email Password Reset Link
                                        </v-btn>

                                        <div class="text-center">
                                            <v-btn :href="route('login')" variant="text" color="primary">
                                                Back to Login
                                            </v-btn>
                                        </div>
                                    </div>
                                </v-form>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
.v-card-text :deep(.v-field__append-inner) {
    padding-inline-start: 12px;
}
</style>