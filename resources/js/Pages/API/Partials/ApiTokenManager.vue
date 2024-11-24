<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import ActionSection from '@/Components/ActionSection.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SectionBorder from '@/Components/SectionBorder.vue';

const props = defineProps({
    tokens: {
        type: Array,
        default: () => [],
    },
    availablePermissions: {
        type: Array,
        default: () => [],
    },
    defaultPermissions: {
        type: Array,
        default: () => [],
    },
});

const createApiTokenForm = useForm({
    name: '',
    permissions: props.defaultPermissions,
});

const updateApiTokenForm = useForm({
    permissions: [],
});

const createDialog = ref(false);
const permissionsDialog = ref(false);
const deleteDialog = ref(false);

const displayingToken = ref(false);
const managingPermissionsFor = ref(null);
const plainTextToken = ref(null);
const tokenBeingDeleted = ref(null);

const createToken = () => {
    createApiTokenForm.post(route('api-tokens.store'), {
        preserveScroll: true,
        onSuccess: (response) => {
            console.log('Token from response:', response.props.jetstream.flash.token);
            plainTextToken.value = response.props.jetstream.flash.token;
            console.log('plainTextToken.value:', plainTextToken.value);
            displayingToken.value = true;
            createDialog.value = false;
            createApiTokenForm.reset();
        },
    });
};
const managePermissions = (token) => {
    managingPermissionsFor.value = token;
    updateApiTokenForm.permissions = token.abilities;
    permissionsDialog.value = true;
};

const updatePermissions = () => {
    if (!managingPermissionsFor.value) return;

    updateApiTokenForm.put(route('api-tokens.update', managingPermissionsFor.value), {
        preserveScroll: true,
        onSuccess: () => {
            permissionsDialog.value = false;
        },
    });
};

const confirmTokenDeletion = (token) => {
    tokenBeingDeleted.value = token;
    deleteDialog.value = true;
};

const deleteApiTokenForm = useForm({}); // Add this with your other form declarations

// Then update the deleteToken method
const deleteToken = () => {
    if (!tokenBeingDeleted.value) return;

    deleteApiTokenForm.delete(route('api-tokens.destroy', tokenBeingDeleted.value), {
        preserveScroll: true,
        onSuccess: () => {
            deleteDialog.value = false;
        },
    });
};

const snackbar = ref(false);
const snackbarText = ref('');

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(plainTextToken.value);
        snackbarText.value = 'API Token copied to clipboard';
        snackbar.value = true;
    } catch (err) {
        snackbarText.value = 'Failed to copy token';
        snackbar.value = true;
        console.error('Failed to copy text: ', err);
    }
};
</script>

<template>
    <div>
        <!-- Generate API Token -->
        <v-card class="mb-6">
            <v-card-title>Create API Token</v-card-title>
            <v-card-text>
                <p class="text-medium-emphasis mb-4">
                    API tokens allow third-party services to authenticate with our application on your behalf.
                </p>

                <v-btn color="primary" @click="createDialog = true">
                    Create New Token
                </v-btn>
            </v-card-text>
        </v-card>

        <!-- API Token List -->
        <v-card v-if="tokens.length > 0">
            <v-card-title>Manage API Tokens</v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th class="text-left">Name</th>
                            <th class="text-left">Last Used</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="token in tokens" :key="token.id">
                            <td>{{ token.name }}</td>
                            <td>
                                <span v-if="token.last_used_at">
                                    Last used {{ token.last_used_ago }}
                                </span>
                                <span v-else class="text-medium-emphasis">Never used</span>
                            </td>
                            <td class="text-right">
                                <v-btn v-if="availablePermissions.length" :icon="true" variant="text" size="small"
                                    class="mr-2" @click="managePermissions(token)">
                                    <v-icon>mdi-cog</v-icon>
                                    <v-tooltip activator="parent">
                                        Permissions
                                    </v-tooltip>
                                </v-btn>
                                <v-btn :icon="true" variant="text" size="small" color="error"
                                    @click="confirmTokenDeletion(token)">
                                    <v-icon>mdi-delete</v-icon>
                                    <v-tooltip activator="parent">
                                        Delete
                                    </v-tooltip>
                                </v-btn>
                            </td>
                        </tr>
                    </tbody>
                </v-table>
            </v-card-text>
        </v-card>

        <!-- Create API Token Dialog -->
        <v-dialog v-model="createDialog" max-width="600px">
            <v-card>
                <v-card-title>Create API Token</v-card-title>
                <v-card-text>
                    <v-form @submit.prevent="createToken">
                        <v-text-field v-model="createApiTokenForm.name" label="Token Name"
                            :error-messages="createApiTokenForm.errors.name" class="mb-4"></v-text-field>

                        <!-- API Token Permissions -->
                        <template v-if="availablePermissions.length">
                            <v-card-subtitle class="px-0">
                                Token Permissions
                            </v-card-subtitle>

                            <v-list>
                                <v-list-item v-for="permission in availablePermissions" :key="permission">
                                    <v-checkbox v-model="createApiTokenForm.permissions" :label="permission"
                                        :value="permission" hide-details></v-checkbox>
                                </v-list-item>
                            </v-list>
                        </template>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="error" variant="text" @click="createDialog = false">
                        Cancel
                    </v-btn>
                    <v-btn color="primary" :loading="createApiTokenForm.processing" @click="createToken">
                        Create
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Token Display Modal -->
        <v-dialog v-model="displayingToken" max-width="600px">
            <v-card>
                <v-card-title>API Token</v-card-title>
                <v-card-text>
                    <p class="mb-4">Please copy your new API token. For your security, it won't be shown again.</p>
                    <v-text-field v-model="plainTextToken" readonly variant="outlined">
                        <template v-slot:append>
                            <v-btn icon="mdi-content-copy" size="small" @click="copyToClipboard">
                                <v-tooltip activator="parent">
                                    Copy to clipboard
                                </v-tooltip>
                            </v-btn>
                        </template>
                    </v-text-field>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" @click="displayingToken = false">
                        Close
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- API Token Permissions Modal -->
        <v-dialog v-model="permissionsDialog" max-width="600px">
            <v-card v-if="managingPermissionsFor">
                <v-card-title>
                    API Token Permissions: {{ managingPermissionsFor.name }}
                </v-card-title>
                <v-card-text>
                    <v-list>
                        <v-list-item v-for="permission in availablePermissions" :key="permission">
                            <v-checkbox v-model="updateApiTokenForm.permissions" :label="permission" :value="permission"
                                hide-details></v-checkbox>
                        </v-list-item>
                    </v-list>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="error" variant="text" @click="permissionsDialog = false">
                        Cancel
                    </v-btn>
                    <v-btn color="primary" :loading="updateApiTokenForm.processing" @click="updatePermissions">
                        Save
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Delete Token Confirmation Dialog -->
        <v-dialog v-model="deleteDialog" max-width="600px">
            <v-card>
                <v-card-title>Delete API Token</v-card-title>
                <v-card-text>
                    Are you sure you would like to delete this API token?
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="error" variant="text" @click="deleteDialog = false">
                        Cancel
                    </v-btn>
                    <v-btn color="primary" @click="deleteToken">
                        Delete
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
    <!-- Add this at the bottom of your template -->
    <v-snackbar v-model="snackbar" :timeout="2000" color="success">
        {{ snackbarText }}
    </v-snackbar>
</template>