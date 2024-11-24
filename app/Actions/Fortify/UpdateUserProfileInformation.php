<template>
  <v-card>
    <v-card-title>Profile Information</v-card-title>
    <v-card-text>
      <v-form @submit.prevent="updateProfileInformation">
        <div v-if="$page.props.jetstream.managesProfilePhotos">
          <!-- Profile Photo -->
          <div class="mb-6">
            <!-- Current Profile Photo -->
            <div v-show="!photoPreview" class="mt-2">
              <img :src="user.profile_photo_url" :alt="user.name" class="rounded-full h-20 w-20 object-cover">
            </div>

            <!-- New Profile Photo Preview -->
            <div v-show="photoPreview" class="mt-2">
              <span
                class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                :style="'background-image: url(\'' + photoPreview + '\');'"
              />
            </div>

            <v-file-input
              ref="photo"
              v-model="form.photo"
              type="file"
              class="mt-2"
              accept="image/*"
              @change="updatePhotoPreview"
              :error-messages="form.errors.photo"
              label="Select A New Photo"
              variant="outlined"
              density="comfortable"
              hide-details
            />
          </div>

          <!-- Name -->
          <v-text-field
            v-model="form.name"
            type="text"
            label="Name"
            :error-messages="form.errors.name"
            variant="outlined"
            density="comfortable"
            class="mt-4"
          />

          <!-- Email -->
          <v-text-field
            v-model="form.email"
            type="email"
            label="Email"
            :error-messages="form.errors.email"
            variant="outlined"
            density="comfortable"
            class="mt-4"
          />

          <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
            <p class="text-sm mt-2">
              Your email address is unverified.
              <Link :href="route('verification.send')" method="post" as="button" class="text-primary">
                Click here to re-send the verification email.
              </Link>
            </p>

            <div v-show="status === 'verification-link-sent'" class="mt-2 text-success text-sm">
              A new verification link has been sent to your email address.
            </div>
          </div>
        </div>

        <div class="d-flex justify-end mt-6">
          <v-btn
            v-if="form.photo"
            type="button"
            class="mr-4"
            @click="clearPhotoInput"
            color="error"
            variant="outlined"
          >
            Remove Photo
          </v-btn>

          <v-btn
            :loading="form.processing"
            color="primary"
            type="submit"
          >
            Save
          </v-btn>
        </div>
      </v-form>
    </v-card-text>
  </v-card>
</template>

<script>
import { defineComponent } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'

export default defineComponent({
  components: {
    Link,
  },

  props: {
    user: {
      type: Object,
      required: true,
    },
  },

  data() {
    return {
      photoPreview: null,
      form: useForm({
        _method: 'PUT',
        name: this.user.name,
        email: this.user.email,
        photo: null,
      }),
    }
  },

  methods: {
    updatePhotoPreview() {
      const photo = this.$refs.photo.files[0]

      if (!photo) {
        return
      }

      const reader = new FileReader()
      reader.onload = (e) => {
        this.photoPreview = e.target.result
      }

      reader.readAsDataURL(photo)
    },

    clearPhotoInput() {
      this.$refs.photo.value = null
      this.form.photo = null
      this.photoPreview = null
    },

    updateProfileInformation() {
      if (this.$refs.photo) {
        this.form.photo = this.$refs.photo.files[0]
      }

      this.form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => {
          this.clearPhotoInput()
        },
      })
    },
  },
})
</script>