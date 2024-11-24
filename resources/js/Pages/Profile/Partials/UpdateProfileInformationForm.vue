<!-- resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.vue -->
<script setup>
import { ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    photo: null,
});

const verificationLinkSent = ref(false);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearPhotoFileInput(),
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];
    if (!photo) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    form.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
</script>

<template>
    <v-card>
        <v-card-title class="text-h6">
            Profile Information
        </v-card-title>

        <v-card-text>
            <div class="text-subtitle-1 mb-4">
                Update your account's profile information and email address.
            </div>

            <!-- Profile Photo -->
            <div v-if="$page.props.jetstream.managesProfilePhotos" class="mb-4">
                <v-label>Photo</v-label>

                <div class="d-flex align-center mt-2">
                    <!-- Current Profile Photo -->
                    <div v-if="!photoPreview" class="mr-4">
                        <v-avatar size="80" :image="user.profile_photo_url"></v-avatar>
                    </div>

                    <!-- New Profile Photo Preview -->
                    <div v-if="photoPreview" class="mr-4">
                        <v-avatar size="80" :image="photoPreview"></v-avatar>
                    </div>

                    <div>
                        <v-btn color="primary" @click="selectNewPhoto" class="mr-2">
                            Select New Photo
                        </v-btn>

                        <v-btn v-if="user.profile_photo_path" color="error" @click="deletePhoto">
                            Remove Photo
                        </v-btn>
                    </div>

                    <input type="file" ref="photoInput" class="d-none" @change="updatePhotoPreview">
                </div>
            </div>

            <v-form @submit.prevent="updateProfileInformation">
                <!-- Name -->
                <v-text-field v-model="form.name" label="Name" :error-messages="form.errors.name" required
                    variant="outlined" prepend-inner-icon="mdi-account" class="mb-4"></v-text-field>

                <!-- Email -->
                <v-text-field v-model="form.email" label="Email" type="email" :error-messages="form.errors.email"
                    required variant="outlined" prepend-inner-icon="mdi-email" class="mb-4"></v-text-field>

                <!-- Email Verification -->
                <div v-if="$page.props.jetstream.hasEmailVerification && !user.email_verified_at">
                    <v-alert type="warning" variant="tonal" class="mb-4">
                        Your email address is unverified.
                        <Link :href="route('verification.send')" method="post" as="button"
                            class="text-primary text-decoration-none" @click="verificationLinkSent = true">
                        Click here to re-send the verification email.
                        </Link>
                    </v-alert>

                    <v-alert v-if="verificationLinkSent" type="success" variant="tonal" class="mb-4">
                        A new verification link has been sent to your email address.
                    </v-alert>
                </div>

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