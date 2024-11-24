<!-- resources/js/Pages/Welcome.vue -->
<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { route } from 'ziggy-js';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String
});

const features = [
    {
        icon: 'mdi-rocket-launch',
        title: 'Quick Start',
        description: 'Built on Laravel 11.x with Vue 3, Inertia.js, and Vuetify'
    },
    {
        icon: 'mdi-shield-lock',
        title: 'Authentication',
        description: 'Complete auth system with social login and team management'
    },
    {
        icon: 'mdi-material-design',
        title: 'Material Design',
        description: 'Beautiful UI components with dark mode support'
    },
    {
        icon: 'mdi-api',
        title: 'API Ready',
        description: 'Built-in API authentication and documentation'
    },
    {
        icon: 'mdi-account-group',
        title: 'Team Management',
        description: 'Collaborative features with role-based permissions'
    },
    {
        icon: 'mdi-test-tube',
        title: 'Testing Ready',
        description: 'Configured with PHPUnit and Pest for robust testing'
    }
];

const isIntersecting = ref({});

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                isIntersecting[entry.target.id] = entry.isIntersecting;
            });
        },
        { threshold: 0.1 }
    );

    document.querySelectorAll('.observe-me').forEach((element) => {
        observer.observe(element);
    });
});
</script>

<template>

    <Head title="Welcome" />

    <v-app>
        <!-- Navigation -->
        <v-app-bar flat>
            <v-spacer />
            <div v-if="canLogin" class="hidden-sm-and-down">
                <template v-if="$page.props.auth.user">
                    <v-btn :href="route('dashboard')" color="primary" class="mr-2">
                        Dashboard
                    </v-btn>
                </template>
                <template v-else>
                    <v-btn :href="route('login')" variant="text" class="mr-2">
                        Log in
                    </v-btn>

                    <v-btn v-if="canRegister" :href="route('register')" color="primary">
                        Register
                    </v-btn>
                </template>
            </div>
        </v-app-bar>

        <v-main class="bg-background">
            <!-- Hero Section -->
            <v-container fluid class="pa-0">
                <v-row no-gutters>
                    <v-col cols="12">
                        <div class="hero-section d-flex align-center justify-center text-center">
                            <div>
                                <h1 class="text-h2 font-weight-bold mb-4">
                                    Laravel App Core
                                </h1>
                                <p class="text-h5 text-medium-emphasis mb-6">
                                    Your foundation for building amazing web applications
                                </p>
                                <v-btn color="primary" size="x-large" class="mr-4" :href="route('register')">
                                    Get Started
                                    <v-icon end>mdi-arrow-right</v-icon>
                                </v-btn>
                                <v-btn variant="outlined" size="x-large"
                                    href="https://github.com/yourusername/laravel-app-core" target="_blank">
                                    <v-icon start>mdi-github</v-icon>
                                    GitHub
                                </v-btn>
                            </div>
                        </div>
                    </v-col>
                </v-row>
            </v-container>

            <!-- Features Grid -->
            <v-container class="py-16">
                <v-row>
                    <v-col v-for="(feature, index) in features" :key="index" cols="12" md="4" class="observe-me"
                        :id="'feature-' + index">
                        <v-card :class="[
                            'feature-card transition-fast-in-fast-out',
                            { 'animate-in': isIntersecting['feature-' + index] }
                        ]" flat class="pa-6 h-100 d-flex flex-column" variant="outlined">
                            <v-icon :icon="feature.icon" size="48" color="primary" class="mb-4" />
                            <h3 class="text-h5 mb-2">{{ feature.title }}</h3>
                            <p class="text-medium-emphasis">{{ feature.description }}</p>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>

            <!-- Tech Stack Section -->
            <v-container fluid class="py-16 secondary-section">
                <v-row justify="center">
                    <v-col cols="12" md="8" class="text-center">
                        <h2 class="text-h3 mb-6">Powered By</h2>
                        <v-row justify="center" align="center">
                            <v-col cols="auto">
                                <div class="tech-logo">
                                    <v-icon size="64" color="primary">mdi-laravel</v-icon>
                                    <div class="text-body-1 mt-2">Laravel v{{ laravelVersion }}</div>
                                </div>
                            </v-col>
                            <v-col cols="auto">
                                <div class="tech-logo">
                                    <v-icon size="64" color="success">mdi-vuejs</v-icon>
                                    <div class="text-body-1 mt-2">Vue 3</div>
                                </div>
                            </v-col>
                            <v-col cols="auto">
                                <div class="tech-logo">
                                    <v-icon size="64" color="info">mdi-language-typescript</v-icon>
                                    <div class="text-body-1 mt-2">TypeScript</div>
                                </div>
                            </v-col>
                        </v-row>
                    </v-col>
                </v-row>
            </v-container>

            <!-- Footer -->
            <v-footer class="bg-background text-center d-flex flex-column">
                <div class="px-4 py-2 text-medium-emphasis">
                    Running PHP v{{ phpVersion }}
                </div>
                <div class="px-4 py-2">
                    <v-btn v-for="icon in ['mdi-facebook', 'mdi-twitter', 'mdi-linkedin', 'mdi-github']" :key="icon"
                        :icon="icon" variant="text" class="mx-2" />
                </div>
            </v-footer>
        </v-main>
    </v-app>
</template>

<style scoped>
.hero-section {
    min-height: 80vh;
    background: linear-gradient(45deg, rgba(var(--v-theme-surface), 0.9), rgba(var(--v-theme-surface), 0.95)),
        url('/hero-background.jpg') center/cover;
    position: relative;
}

.secondary-section {
    background-color: rgb(var(--v-theme-surface));
}

.feature-card {
    opacity: 0;
    transform: translateY(20px);
}

.feature-card.animate-in {
    opacity: 1;
    transform: translateY(0);
    transition: all 0.6s ease-out;
}

.tech-logo {
    padding: 2rem;
    text-align: center;
    transition: transform 0.3s ease;
}

.tech-logo:hover {
    transform: translateY(-5px);
}

.transition-fast-in-fast-out {
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>