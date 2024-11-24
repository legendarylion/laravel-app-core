<!-- resources/js/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue -->
<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    requiresConfirmation: Boolean,
    qrCode: String,
    recoveryCodes: Array,
});

const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const recoveringCodes = ref(false);
const showingRecoveryCodes = ref(false);
const showingConfirmation = ref(false);
const confirmationCode = ref('');

const form = useForm({});
const confirmationForm = useForm({
    code: '',
});

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post(route('two-factor.confirm'), {
        errorBag: 'confirmTwoFactorAuthentication',
        preserveScroll: true,
        onSuccess: () => {
            showingRecoveryCodes.value = true;
            confirming.value = false;
        },
    });
};

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    form.post(route('two-factor.enable'), {
        preserveScroll: true,
        onSuccess: () => {
            confirming.value = true;
            enabling.value = false;
        },
    });
};

const regenerateRecoveryCodes = () => {
    recoveringCodes.value = true;

    form.post(route('two-factor.recovery-codes'), {
        preserveScroll: true,
        onSuccess: () => {
            showingRecoveryCodes.value = true;
            recoveringCodes.value = false;
        },
    });
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    form.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
};

const twoFactorEnabled = computed(() => {
    return !enabling.value && props.requiresConfirmation;
});
</script>

<template>
    <v-card>
        <v-card-title class="text-h6">
            Two Factor Authentication
        </v-card-title>

        <v-card-text>
            <div class="text-subtitle-1 mb-4">
                Add additional security to your account using two factor authentication.
            </div>

            <template v-if="twoFactorEnabled">
                <div v-if="showingRecoveryCodes">
                    <div class="text-subtitle-1 mb-4">
                        Store these recovery codes in a secure password manager. They can be used to recover access to
                        your account if your two factor authentication device is lost.
                    </div>

                    <v-card variant="outlined" class="mb-4">
                        <v-card-text class="font-monospace">
                            <div v-for="code in recoveryCodes" :key="code" class="mb-1">
                                {{ code }}
                            </div>
                        </v-card-text>
                    </v-card>
                </div>

                <v-card-actions>
                    <v-btn color="warning" :loading="recoveringCodes" @click="regenerateRecoveryCodes" class="mr-2">
                        Regenerate Recovery Codes
                    </v-btn>

                    <v-btn color="error" :loading="disabling" @click="disableTwoFactorAuthentication">
                        Disable 2FA
                    </v-btn>
                </v-card-actions>
            </template>

            <template v-else>
                <div v-if="confirming">
                    <div class="mb-4">
                        <div class="text-subtitle-1 mb-2">
                            To finish enabling two factor authentication, scan the following QR code using your phone's
                            authenticator application or enter the setup key and provide the generated OTP code.
                        </div>

                        <div v-html="qrCode" class="mb-4"></div>

                        <v-form @submit.prevent="confirmTwoFactorAuthentication">
                            <v-text-field v-model="confirmationForm.code" label="Code"
                                :error-messages="confirmationForm.errors.code" variant="outlined"
                                prepend-inner-icon="mdi-lock-check" class="mb-4"></v-text-field>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn type="submit" color="primary" :loading="confirmationForm.processing">
                                    Confirm
                                </v-btn>
                            </v-card-actions>
                        </v-form>
                    </div>
                </div>

                <v-card-actions v-else>
                    <v-btn color="primary" :loading="enabling" @click="enableTwoFactorAuthentication">
                        Enable 2FA
                    </v-btn>
                </v-card-actions>
            </template>
        </v-card-text>
    </v-card>
</template>