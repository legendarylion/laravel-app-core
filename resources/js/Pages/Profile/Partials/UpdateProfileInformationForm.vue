<script setup>
import { ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
    _method: 'PUT',
    name: user?.name || '',
    email: user?.email || '',
    photo: null,
});

const verificationLinkSent = ref(false);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
    // Create FormData instance
    const formData = new FormData();

    // Always append these required fields
    formData.append('name', form.name);
    formData.append('email', form.email);

    // Add photo if selected
    if (photoInput.value?.files[0]) {
        formData.append('photo', photoInput.value.files[0]);
        console.log('Attaching photo:', photoInput.value.files[0].name);
    }

    // Debug log
    console.log('Submitting form with data:', {
        name: formData.get('name'),
        email: formData.get('email'),
        hasPhoto: formData.has('photo')
    });

    form.submit('put', route('user-profile-information.update'), {
        preserveScroll: true,
        forceFormData: true,
        data: formData,
        onSuccess: () => {
            console.log('Profile update successful');
            clearPhotoFileInput();
            window.location.reload();
        },
        onError: (errors) => {
            console.error('Profile update errors:', errors);
        }
    });
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];
    if (!photo) return;

    // Check file size
    if (photo.size > 2 * 1024 * 1024) {
        alert('File size must be less than 2MB');
        clearPhotoFileInput();
        return;
    }

    console.log('Photo selected:', {
        name: photo.name,
        type: photo.type,
        size: photo.size
    });

    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(photo);
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
    photoPreview.value = null;
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
</script>

<template>
    <v-card>
        <v-card-title class="text-h6">
            Profile Information
        </v-card-title>

        <v-card-text>
            <form @submit.prevent="updateProfileInformation" enctype="multipart/form-data">
                <!-- Profile Photo -->
                <div v-if="$page.props.jetstream.managesProfilePhotos" class="mb-4">
                    <v-label>Photo</v-label>

                    <div class="d-flex align-center mt-2">
                        <!-- Current Profile Photo -->
                        <div v-if="!photoPreview" class="mr-4">
                            <v-avatar size="80">
                                <v-img :src="user.profile_photo_url" :alt="user.name"></v-img>
                            </v-avatar>
                        </div>

                        <!-- New Profile Photo Preview -->
                        <div v-if="photoPreview" class="mr-4">
                            <v-avatar size="80">
                                <v-img :src="photoPreview" :alt="user.name"></v-img>
                            </v-avatar>
                        </div>

                        <div>
                            <v-btn color="primary" @click="selectNewPhoto" class="mr-2">
                                Select New Photo
                            </v-btn>

                            <v-btn v-if="user.profile_photo_path" color="error" @click="deletePhoto">
                                Remove Photo
                            </v-btn>
                        </div>

                        <input type="file" ref="photoInput" class="d-none" @change="updatePhotoPreview"
                            accept="image/*">
                    </div>
                </div>

                <!-- Name -->
                <v-text-field v-model="form.name" label="Name" :error-messages="form.errors.name" required
                    variant="outlined" prepend-inner-icon="mdi-account" class="mb-4"></v-text-field>

                <!-- Email -->
                <v-text-field v-model="form.email" label="Email" type="email" :error-messages="form.errors.email"
                    required variant="outlined" prepend-inner-icon="mdi-email" class="mb-4"></v-text-field>

                <!-- Submit Button -->
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn type="submit" color="primary" :loading="form.processing" :disabled="form.processing">
                        Save
                    </v-btn>
                </v-card-actions>

                <!-- Error Display -->
                <div v-if="Object.keys(form.errors).length > 0" class="mt-4 text-red">
                    <div v-for="(error, key) in form.errors" :key="key" class="mb-1">
                        {{ error }}
                    </div>
                </div>
            </form>
        </v-card-text>
    </v-card>
</template>