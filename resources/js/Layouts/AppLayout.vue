<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const teams = computed(() => page.props.jetstream?.hasTeamFeatures);
const userTeams = computed(() => page.props.auth?.user?.all_teams || []);

const drawer = ref(false);
const theme = ref('dark');
const showSearch = ref(false);
const searchQuery = ref('');
const showNotifications = ref(false);

// Navigation items with proper route checking
const navigation = computed(() => {
    const items = [
        {
            title: 'Dashboard',
            icon: 'mdi-view-dashboard-outline',
            href: route('dashboard'),
            active: route().current('dashboard'),
            show: true
        },
        {
            title: 'Profile',
            icon: 'mdi-account-outline',
            href: route('profile.show'),
            active: route().current('profile.show'),
            show: true
        }
    ];

    if (teams.value && user.value?.current_team) {
        items.push({
            title: 'Teams',
            icon: 'mdi-account-group-outline',
            href: route('teams.show', { team: user.value.current_team.id }),
            active: route().current('teams.show', { team: user.value.current_team.id }),
            show: teams.value
        });
    }

    return items;
});

// Secondary navigation with proper route handling
const secondaryNavigation = computed(() => {
    const items = [
        {
            title: 'Settings',
            icon: 'mdi-cog-outline',
            href: route('profile.show'),
            active: route().current('profile.show'),
            show: true
        }
    ];

    // Only add API Tokens if the route exists
    try {
        if (route().has('api-tokens.index')) {
            items.push({
                title: 'API Tokens',
                icon: 'mdi-key-outline',
                href: route('api-tokens.index'),
                active: route().current('api-tokens.index'),
                show: true
            });
        }
    } catch (error) {
        console.warn('API tokens route not available');
    }

    return items;
});

// Quick actions
const quickActions = [
    {
        title: 'New Project',
        icon: 'mdi-folder-plus-outline',
        action: () => console.log('New Project')
    },
    {
        title: 'Upload Files',
        icon: 'mdi-cloud-upload-outline',
        action: () => console.log('Upload Files')
    },
    {
        title: 'Generate Report',
        icon: 'mdi-file-chart-outline',
        action: () => console.log('Generate Report')
    }
];

// Notifications
const notifications = ref([
    {
        id: 1,
        type: 'info',
        message: 'System update scheduled for tonight',
        time: '2 hours ago',
        read: false
    },
    {
        id: 2,
        type: 'warning',
        message: 'Storage space running low',
        time: '5 hours ago',
        read: false
    }
]);

// Modified breadcrumbs with better error handling
const breadcrumbs = computed(() => {
    try {
        const current = route().current();
        if (!current) return [];

        const segments = current.split('.');
        return segments.map((segment, index) => ({
            title: segment.charAt(0).toUpperCase() + segment.slice(1),
            href: route(segments.slice(0, index + 1).join('.')),
            disabled: index === segments.length - 1
        }));
    } catch (error) {
        console.warn('Error generating breadcrumbs:', error);
        return [];
    }
});

// Actions
const toggleTheme = () => {
    theme.value = theme.value === 'light' ? 'dark' : 'light';
};

const toggleSearch = async () => {
    showSearch.value = !showSearch.value;
    if (showSearch.value) {
        await nextTick();
        document.getElementById('search-input')?.focus();
    }
};

const performSearch = () => {
    console.log('Searching for:', searchQuery.value);
    // Implement search functionality
};

const markAsRead = (id) => {
    const notification = notifications.value.find(n => n.id === id);
    if (notification) notification.read = true;
};

const logout = () => {
    router.post(route('logout'));
};

const handleVisibility = (visible) => {
    const mainContent = document.querySelector('.v-main');
    if (mainContent) {
        if (visible) {
            mainContent.classList.add('content-blur');
        } else {
            mainContent.classList.remove('content-blur');
        }
    }
};

// Watch drawer state
watch(drawer, handleVisibility);
</script>

<template>
    <v-app>
        <!-- Navigation Drawer -->
        <v-navigation-drawer v-model="drawer" :rail="false" permanent class="elevation-3">
            <!-- App Logo / Brand -->
            <div class="px-2 py-4">
                <v-list-item :prepend-avatar="user?.profile_photo_url" :title="user?.name" :subtitle="user?.email"
                    class="mb-2" />
            </div>

            <v-divider></v-divider>

            <!-- Primary Navigation -->
            <v-list nav density="comfortable">
                <template v-for="item in navigation" :key="item.title">
                    <v-list-item v-if="item.show" :href="item.href" :prepend-icon="item.icon" :title="item.title"
                        :active="item.active" :value="item.title" class="mb-1" rounded="lg"
                        @click.prevent="router.visit(item.href)">
                        <template v-slot:append v-if="item.badge">
                            <v-chip size="small" :color="item.badge.color || 'primary'">
                                {{ item.badge.text }}
                            </v-chip>
                        </template>
                    </v-list-item>
                </template>
            </v-list>

            <template v-slot:append>
                <v-divider class="mb-2"></v-divider>

                <!-- Secondary Navigation -->
                <v-list nav density="comfortable">
                    <template v-for="item in secondaryNavigation" :key="item.title">
                        <v-list-item v-if="item.show" :href="item.href" :prepend-icon="item.icon" :title="item.title"
                            :value="item.title" rounded="lg" class="mb-1" @click.prevent="router.visit(item.href)" />
                    </template>

                    <v-list-item @click="logout" prepend-icon="mdi-logout" title="Logout" rounded="lg"
                        class="mb-2"></v-list-item>
                </v-list>
            </template>
        </v-navigation-drawer>

        <!-- Top App Bar -->
        <v-app-bar elevation="0">
            <v-app-bar-nav-icon @click="drawer = !drawer"
                :icon="drawer ? 'mdi-close' : 'mdi-menu'"></v-app-bar-nav-icon>

            <v-app-bar-title class="text-capitalize">
                {{ route().current()?.split('.').pop() || 'Dashboard' }}
            </v-app-bar-title>

            <!-- Search Field -->
            <v-slide-x-transition>
                <v-text-field v-if="showSearch" v-model="searchQuery" id="search-input" density="compact"
                    variant="outlined" hide-details placeholder="Search..." prepend-inner-icon="mdi-magnify"
                    class="mx-4" @keyup.enter="performSearch"></v-text-field>
            </v-slide-x-transition>

            <template v-slot:append>
                <!-- Search Toggle -->
                <v-btn icon class="mr-2" @click="toggleSearch">
                    <v-icon>mdi-magnify</v-icon>
                </v-btn>

                <!-- Theme Toggle -->
                <v-btn icon class="mr-2" @click="toggleTheme">
                    <v-icon>{{ theme === 'dark' ? 'mdi-weather-sunny' : 'mdi-weather-night' }}</v-icon>
                </v-btn>

                <!-- Quick Actions -->
                <v-speed-dial direction="bottom" transition="slide-y-reverse-transition" class="mr-2">
                    <template v-slot:activator>
                        <v-btn color="primary" icon>
                            <v-icon>mdi-plus</v-icon>
                        </v-btn>
                    </template>

                    <v-btn v-for="action in quickActions" :key="action.title" color="primary" icon
                        @click="action.action">
                        <v-icon>{{ action.icon }}</v-icon>
                        <v-tooltip activator="parent" location="left">
                            {{ action.title }}
                        </v-tooltip>
                    </v-btn>
                </v-speed-dial>

                <!-- Notifications -->
                <v-menu v-model="showNotifications" :close-on-content-click="false" location="bottom end"
                    min-width="300">
                    <template v-slot:activator="{ props }">
                        <v-btn icon v-bind="props" class="mr-2">
                            <v-icon>mdi-bell-outline</v-icon>
                            <v-badge :content="notifications.filter(n => !n.read).length" color="error"
                                v-if="notifications.some(n => !n.read)"></v-badge>
                        </v-btn>
                    </template>

                    <v-card>
                        <v-list>
                            <v-list-subheader>Notifications</v-list-subheader>
                            <v-list-item v-for="notification in notifications" :key="notification.id"
                                :class="{ 'bg-grey-darken-3': !notification.read }"
                                @click="markAsRead(notification.id)">
                                <template v-slot:prepend>
                                    <v-icon :color="notification.type === 'info' ? 'info' : 'warning'">
                                        {{ notification.type === 'info' ? 'mdi-information' : 'mdi-alert' }}
                                    </v-icon>
                                </template>
                                <v-list-item-title>{{ notification.message }}</v-list-item-title>
                                <v-list-item-subtitle>{{ notification.time }}</v-list-item-subtitle>
                            </v-list-item>
                        </v-list>
                    </v-card>
                </v-menu>

                <!-- User Menu -->
                <v-menu transition="slide-y-transition" location="bottom end">
                    <template v-slot:activator="{ props }">
                        <v-avatar v-bind="props" :image="user?.profile_photo_url" size="35"
                            class="cursor-pointer"></v-avatar>
                    </template>

                    <v-list width="200">
                        <v-list-item :title="user?.name" :subtitle="user?.email" class="mb-2"></v-list-item>

                        <v-divider></v-divider>

                        <template v-for="item in secondaryNavigation" :key="item.title">
                            <v-list-item v-if="item.show" :href="item.href" :prepend-icon="item.icon"
                                :title="item.title" @click.prevent="router.visit(item.href)" />
                        </template>

                        <v-divider></v-divider>

                        <v-list-item @click="logout" prepend-icon="mdi-logout" title="Logout"
                            color="error"></v-list-item>
                    </v-list>
                </v-menu>
            </template>
        </v-app-bar>

        <!-- Breadcrumbs -->
        <v-breadcrumbs v-if="breadcrumbs.length > 0" :items="breadcrumbs" class="px-4 py-2">
            <template v-slot:divider>
                <v-icon icon="mdi-chevron-right"></v-icon>
            </template>
        </v-breadcrumbs>

        <!-- Main Content -->
        <v-main>
            <div v-show="drawer" class="backdrop-overlay" @click="drawer = false; handleVisibility(false)"></div>
            <slot></slot>
        </v-main>
    </v-app>
</template>

<style scoped>
.v-navigation-drawer {
    border-right: 0 !important;
}

.backdrop-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 5;
    transition: all 0.3s ease;
    pointer-events: auto;
}

:deep(.content-blur) {
    transition: all 0.3s ease;
    filter: blur(4px);
}

.cursor-pointer {
    cursor: pointer;
}

:deep(.v-navigation-drawer) {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 10 !important;
}

:deep(.v-app-bar) {
    z-index: 15 !important;
}

:deep(.v-speed-dial) {
    margin-right: 8px;
}

:deep(.v-speed-dial__list) {
    padding-top: 8px;
}
</style>