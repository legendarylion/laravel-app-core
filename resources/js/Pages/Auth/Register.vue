<!-- resources/js/Pages/Auth/Register.vue -->
<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <v-app>
        <v-main>
            <v-container fluid class="fill-height">
                <v-row justify="center" align="center">
                    <v-col cols="12" sm="8" md="6" lg="4">
                        <v-card class="elevation-12">
                            <v-toolbar color="primary" flat>
                                <v-toolbar-title>Register</v-toolbar-title>
                            </v-toolbar>

                            <v-card-text class="pa-6">
                                <v-form @submit.prevent="submit">
                                    <!-- Name -->
                                    <v-text-field v-model="form.name" :error-messages="form.errors.name" label="Name"
                                        name="name" type="text" required variant="outlined" autocomplete="name"
                                        prepend-icon="mdi-account" class="mt-4" persistent-placeholder
                                        :append-inner-icon="null" />

                                    <!-- Email -->
                                    <v-text-field v-model="form.email" :error-messages="form.errors.email" label="Email"
                                        name="email" type="email" required variant="outlined" autocomplete="username"
                                        prepend-icon="mdi-email" persistent-placeholder :append-inner-icon="null" />

                                    <!-- Password -->
                                    <v-text-field v-model="form.password" :error-messages="form.errors.password"
                                        :type="showPassword ? 'text' : 'password'" label="Password" name="password"
                                        required variant="outlined" autocomplete="new-password" prepend-icon="mdi-lock"
                                        persistent-placeholder
                                        :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                                        @click:append-inner="showPassword = !showPassword" />

                                    <!-- Password Confirmation -->
                                    <v-text-field v-model="form.password_confirmation"
                                        :error-messages="form.errors.password_confirmation"
                                        :type="showPasswordConfirmation ? 'text' : 'password'" label="Confirm Password"
                                        name="password_confirmation" required variant="outlined"
                                        autocomplete="new-password" prepend-icon="mdi-lock-check" persistent-placeholder
                                        :append-inner-icon="showPasswordConfirmation ? 'mdi-eye' : 'mdi-eye-off'"
                                        @click:append-inner="showPasswordConfirmation = !showPasswordConfirmation" />

                                    <!-- Terms and Conditions -->
                                    <v-checkbox v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
                                        v-model="form.terms" :error-messages="form.errors.terms" color="primary"
                                        class="mt-2">
                                        <template v-slot:label>
                                            <div class="text-wrap">
                                                I agree to the
                                                <a :href="route('terms.show')" class="text-primary text-decoration-none"
                                                    target="_blank">
                                                    Terms of Service
                                                </a>
                                                and
                                                <a :href="route('policy.show')"
                                                    class="text-primary text-decoration-none" target="_blank">
                                                    Privacy Policy
                                                </a>
                                            </div>
                                        </template>
                                    </v-checkbox>

                                    <div class="d-flex flex-column gap-4 mt-2">
                                        <!-- Register Button -->
                                        <v-btn type="submit" color="primary" block :loading="form.processing"
                                            :disabled="form.processing" elevation="2">
                                            Register
                                        </v-btn>

                                        <!-- Already Registered Link -->
                                        <div class="text-center">
                                            <v-btn :href="route('login')" variant="text" color="primary">
                                                Already registered?
                                            </v-btn>
                                        </div>

                                        <v-divider class="my-4">
                                            <span class="text-medium-emphasis">Or register with</span>
                                        </v-divider>

                                        <!-- Social Registration -->
                                        <v-row dense>
                                            <v-col cols="6">
                                                <v-btn :href="route('socialite.redirect', 'google')" color="error" block
                                                    prepend-icon="mdi-google" elevation="2">
                                                    Google
                                                </v-btn>
                                            </v-col>

                                            <v-col cols="6">
                                                <v-btn :href="route('socialite.redirect', 'facebook')" color="#1877F2"
                                                    block prepend-icon="mdi-facebook" elevation="2">
                                                    Facebook
                                                </v-btn>
                                            </v-col>
                                        </v-row>
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

.text-wrap {
    white-space: normal;
}
</style>