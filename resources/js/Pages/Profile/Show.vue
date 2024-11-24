<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from './Partials/LogoutOtherBrowserSessionsForm.vue';
import TwoFactorAuthenticationForm from './Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <v-container fluid class="pa-0">
                <h2 class="text-h5 font-weight-medium">Profile Settings</h2>
            </v-container>
        </template>

        <v-container>
            <v-row>
                <v-col cols="12" lg="8">
                    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                        <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                            <UpdateProfileInformationForm :user="$page.props.auth.user" />

                            <v-divider class="my-8" />
                        </div>

                        <div v-if="$page.props.jetstream.canUpdatePassword">
                            <UpdatePasswordForm />

                            <v-divider class="my-8" />
                        </div>

                        <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
                            <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication" />

                            <v-divider class="my-8" />
                        </div>

                        <LogoutOtherBrowserSessionsForm :sessions="sessions" />

                        <template v-if="
                            $page.props.jetstream.hasAccountDeletionFeatures
                        ">
                            <v-divider class="my-8" />

                            <DeleteUserForm />
                        </template>
                    </div>
                </v-col>
            </v-row>
        </v-container>
    </AppLayout>
</template>