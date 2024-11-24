<!-- resources/js/Pages/Profile/Show.vue -->
<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import TwoFactorAuthenticationForm from './Partials/TwoFactorAuthenticationForm.vue';
import LogoutOtherBrowserSessionsForm from './Partials/LogoutOtherBrowserSessionsForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';

defineProps({
    sessions: Array,
    confirmsTwoFactorAuthentication: Boolean,
});

const tab = ref(0);
</script>

<template>
    <AppLayout>

        <Head title="Profile" />

        <v-container>
            <v-row>
                <v-col cols="12" md="3">
                    <v-card class="mb-4">
                        <v-tabs v-model="tab" direction="vertical" color="primary">
                            <v-tab value="0">
                                <v-icon start>mdi-account-outline</v-icon>
                                Profile
                            </v-tab>
                            <v-tab value="1">
                                <v-icon start>mdi-lock-outline</v-icon>
                                Password
                            </v-tab>
                            <v-tab value="2">
                                <v-icon start>mdi-shield-lock-outline</v-icon>
                                Two Factor Auth
                            </v-tab>
                            <v-tab value="3">
                                <v-icon start>mdi-laptop</v-icon>
                                Browser Sessions
                            </v-tab>
                            <v-tab value="4">
                                <v-icon start>mdi-delete-outline</v-icon>
                                Delete Account
                            </v-tab>
                        </v-tabs>
                    </v-card>
                </v-col>

                <v-col cols="12" md="9">
                    <v-window v-model="tab">
                        <v-window-item value="0">
                            <UpdateProfileInformationForm />
                        </v-window-item>

                        <v-window-item value="1">
                            <UpdatePasswordForm />
                        </v-window-item>

                        <v-window-item value="2">
                            <TwoFactorAuthenticationForm
                                :confirms-two-factor-authentication="confirmsTwoFactorAuthentication" />
                        </v-window-item>

                        <v-window-item value="3">
                            <LogoutOtherBrowserSessionsForm :sessions="sessions" />
                        </v-window-item>

                        <v-window-item value="4">
                            <DeleteUserForm />
                        </v-window-item>
                    </v-window>
                </v-col>
            </v-row>
        </v-container>
    </AppLayout>
</template>