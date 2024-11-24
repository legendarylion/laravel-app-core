<!-- resources/js/Pages/Dashboard.vue -->
<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const stats = [
    {
        title: 'Total Users',
        value: '2,149',
        icon: 'mdi-account-group',
        color: 'primary'
    },
    {
        title: 'Active Teams',
        value: '8',
        icon: 'mdi-account-multiple-check',
        color: 'success'
    },
    {
        title: 'API Requests',
        value: '28.4k',
        icon: 'mdi-api',
        color: 'info'
    },
    {
        title: 'Storage Used',
        value: '2.1 GB',
        icon: 'mdi-database',
        color: 'warning'
    }
];

const recentActivity = [
    {
        user: 'John Doe',
        action: 'Created new team',
        time: '2 minutes ago',
        icon: 'mdi-account-group',
        color: 'success'
    },
    {
        user: 'Jane Smith',
        action: 'Generated API token',
        time: '1 hour ago',
        icon: 'mdi-key',
        color: 'warning'
    },
    {
        user: 'Mike Johnson',
        action: 'Updated profile',
        time: '3 hours ago',
        icon: 'mdi-account-edit',
        color: 'info'
    }
];

// You can replace this with real data from your backend
const chartData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [{
        label: 'API Requests',
        data: [65, 59, 80, 81, 56, 55],
        fill: false,
        borderColor: 'rgb(75, 192, 192)',
        tension: 0.1
    }]
};
</script>

<template>
    <AppLayout>

        <Head title="Dashboard" />

        <v-container fluid>
            <!-- Stats Cards Row -->
            <v-row>
                <v-col v-for="stat in stats" :key="stat.title" cols="12" sm="6" md="3">
                    <v-card class="mx-auto">
                        <v-card-text>
                            <div class="d-flex justify-space-between align-center">
                                <div>
                                    <div class="text-overline mb-1">
                                        {{ stat.title }}
                                    </div>
                                    <div class="text-h4 mb-1">
                                        {{ stat.value }}
                                    </div>
                                </div>
                                <v-avatar :color="stat.color" size="44">
                                    <v-icon :icon="stat.icon" size="24" color="white"></v-icon>
                                </v-avatar>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Main Content Row -->
            <v-row class="mt-4">
                <!-- Chart Card -->
                <v-col cols="12" md="8">
                    <v-card>
                        <v-card-title class="text-h6">
                            API Usage
                            <v-spacer></v-spacer>
                            <v-btn variant="text" color="primary" prepend-icon="mdi-download">
                                Download Report
                            </v-btn>
                        </v-card-title>
                        <v-card-text>
                            <div class="d-flex justify-center align-center" style="height: 300px;">
                                <!-- Replace this with your preferred charting component -->
                                <div class="text-subtitle-1">Chart Component Goes Here</div>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

                <!-- Recent Activity Card -->
                <v-col cols="12" md="4">
                    <v-card>
                        <v-card-title class="text-h6">
                            Recent Activity
                        </v-card-title>

                        <v-card-text class="pa-0">
                            <v-timeline density="compact" align="start">
                                <v-timeline-item v-for="activity in recentActivity" :key="activity.time"
                                    :dot-color="activity.color" size="small">
                                    <template v-slot:icon>
                                        <v-icon :icon="activity.icon"></v-icon>
                                    </template>
                                    <div class="mb-4">
                                        <div class="font-weight-medium">
                                            {{ activity.user }}
                                        </div>
                                        <div class="text-caption">
                                            {{ activity.action }}
                                        </div>
                                        <div class="text-caption text-medium-emphasis">
                                            {{ activity.time }}
                                        </div>
                                    </div>
                                </v-timeline-item>
                            </v-timeline>
                        </v-card-text>

                        <v-card-actions class="justify-center pb-4">
                            <v-btn variant="text" color="primary">
                                View All Activity
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Quick Actions Row -->
            <v-row class="mt-4">
                <v-col cols="12">
                    <v-card>
                        <v-card-title class="text-h6">
                            Quick Actions
                        </v-card-title>
                        <v-card-text>
                            <v-row>
                                <v-col cols="12" sm="6" md="3">
                                    <v-btn block color="primary" prepend-icon="mdi-account-plus" variant="elevated">
                                        Add New User
                                    </v-btn>
                                </v-col>
                                <v-col cols="12" sm="6" md="3">
                                    <v-btn block color="success" prepend-icon="mdi-account-group-outline"
                                        variant="elevated">
                                        Create Team
                                    </v-btn>
                                </v-col>
                                <v-col cols="12" sm="6" md="3">
                                    <v-btn block color="info" prepend-icon="mdi-key-outline" variant="elevated">
                                        Generate API Key
                                    </v-btn>
                                </v-col>
                                <v-col cols="12" sm="6" md="3">
                                    <v-btn block color="warning" prepend-icon="mdi-cog-outline" variant="elevated">
                                        Settings
                                    </v-btn>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </AppLayout>
</template>