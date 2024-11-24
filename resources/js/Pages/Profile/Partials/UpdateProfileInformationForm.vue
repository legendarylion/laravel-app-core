<script setup>
import { ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const props = defineProps({
    user: Object,
});

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    photo: null,
});

const verificationLinkSent = ref(null);
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
        <v-card-title>Profile Information</v-card-title>
        <v-card-text>
            <v-form @submit.prevent="updateProfileInformation">
                <!-- Profile Photo -->
                <div v-if="$page.props.jetstream.managesProfilePhotos">
                    <v-card-subtitle>Photo</v-card-subtitle>

                    <!-- New Profile Photo Preview -->
                    <div class="mt-2" v-if="photoPreview">
                        <v-img :src="photoPreview" width="100" height="100" cover class="rounded-circle" />
                    </div>

                    <template v-else>
                        <v-img :src="user.profile_photo_url" :alt="user.name" width="100" height="100" cover
                            class="rounded-circle" />
                    </template>

                    <v-row class="mt-4">
                        <v-col>
                            <v-btn color="secondary" @click="selectNewPhoto" class="mr-2">
                                Select A New Photo
                            </v-btn>

                            <v-btn v-if="user.profile_photo_path" type="button" color="error" @click="deletePhoto">
                                Remove Photo
                            </v-btn>
                        </v-col>
                    </v-row>

                    <input type="file" ref="photoInput" @change="updatePhotoPreview" hidden />
                </div>

                <!-- Name -->
                <v-text-field v-model="form.name" label="Name" required :error-messages="form.errors.name" class="mt-4"
                    variant="outlined" />

                <!-- Email -->
                <v-text-field v-model="form.email" label="Email" required :error-messages="form.errors.email"
                    variant="outlined" />

                <div v-if="
                        $page.props.jetstream.hasEmailVerification &&
                        user.email_verified_at === null
                    ">
                    <p class="text-sm mt-2">
                        Your email address is unverified.

                        <Link :href="route('verification.send')" method="post" as="button"
                            class="text-decoration-underline text-primary" @click="sendEmailVerification">
                        Click here to re-send the verification email.
                        </Link>
                    </p>

                    <div v-show="verificationLinkSent" class="mt-2 text-success text-sm">
                        A new verification link has been sent to your email
                        address.
                    </div>
                </div>
            </v-form>
        </v-card-text>

        <v-card-actions>
            <v-spacer />
            <v-btn color="primary" :loading="form.processing" @click="updateProfileInformation">
                Save
            </v-btn>
        </v-card-actions>
    </v-card>
</template>