<!-- resources/js/Pages/Auth/Login.vue -->
<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
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
                                <v-toolbar-title>Login</v-toolbar-title>
                            </v-toolbar>

                            <v-card-text class="pa-6">
                                <v-form @submit.prevent="submit">
                                    <v-text-field v-model="form.email" :error-messages="form.errors.email" label="Email"
                                        name="email" prepend-icon="mdi-email" type="email" required variant="outlined"
                                        autocomplete="username" class="mt-4"
                                        :append-inner-icon="null" />

                                    <v-text-field v-model="form.password" :error-messages="form.errors.password"
                                        :type="showPassword ? 'text' : 'password'" label="Password" name="password"
                                        prepend-icon="mdi-lock" required variant="outlined"
                                        autocomplete="current-password"
                                        :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                                        @click:append-inner="showPassword = !showPassword" />

                                    <v-checkbox v-model="form.remember" label="Remember me" color="primary"
                                        class="mt-2" />

                                    <div class="d-flex flex-column gap-4 mt-2">
                                        <v-btn type="submit" color="primary" block :loading="form.processing"
                                            :disabled="form.processing" elevation="2">
                                            Log in
                                        </v-btn>

                                        <div class="text-center">
                                            <v-btn :href="route('password.request')" variant="text" color="primary">
                                                Forgot your password?
                                            </v-btn>
                                        </div>

                                        <v-divider class="my-4">
                                            <span class="text-medium-emphasis">Or continue with</span>
                                        </v-divider>

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
</style>

