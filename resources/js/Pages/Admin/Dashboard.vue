<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Line, Doughnut, Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const props = defineProps({
    stats: Object,
    revenueChart: Object,
    topProducts: Array,
    topSellers: Array,
    recentOrders: Array,
    userStats: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        processing: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        paid: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        failed: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        cancelled: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
    };
    return colors[status] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400';
};

// Revenue Chart Configuration
const revenueChartData = {
    labels: props.revenueChart.labels,
    datasets: [
        {
            label: 'Revenue',
            data: props.revenueChart.revenue,
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            fill: true,
            tension: 0.4,
            borderWidth: 2,
            pointRadius: 0,
            pointHoverRadius: 4,
        },
    ],
};

const revenueChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        mode: 'index',
        intersect: false,
    },
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: 'rgba(17, 24, 39, 0.9)',
            padding: 12,
            titleFont: { size: 13 },
            bodyFont: { size: 12 },
            cornerRadius: 8,
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                color: '#9CA3AF',
                font: { size: 11 },
                maxTicksLimit: 7,
            },
            border: {
                display: false,
            },
        },
        y: {
            grid: {
                color: 'rgba(156, 163, 175, 0.1)',
            },
            ticks: {
                color: '#9CA3AF',
                font: { size: 11 },
                callback: (value) => '$' + value.toLocaleString(),
            },
            border: {
                display: false,
            },
        },
    },
};

// Orders Chart
const ordersChartData = {
    labels: props.revenueChart.labels,
    datasets: [
        {
            label: 'Orders',
            data: props.revenueChart.orders,
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderRadius: 4,
            barThickness: 8,
        },
    ],
};

const ordersChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(17, 24, 39, 0.9)',
            padding: 12,
            cornerRadius: 8,
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { display: false },
            border: { display: false },
        },
        y: {
            grid: { display: false },
            ticks: { display: false },
            border: { display: false },
        },
    },
};

// User Role Distribution Chart
const userRoleChartData = {
    labels: ['Admin', 'Seller', 'Buyer'],
    datasets: [
        {
            data: [
                props.userStats.byRole.admin,
                props.userStats.byRole.seller,
                props.userStats.byRole.buyer,
            ],
            backgroundColor: [
                'rgba(139, 92, 246, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
            ],
            borderWidth: 0,
            cutout: '75%',
        },
    ],
};

const userRoleChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(17, 24, 39, 0.9)',
            padding: 12,
            cornerRadius: 8,
        },
    },
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Dashboard</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Welcome back, here's what's happening</p>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Revenue Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</span>
                    <div class="p-2 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(stats.totalRevenue) }}</p>
                <div class="flex items-center mt-2">
                    <span
                        :class="stats.revenueGrowth >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                        class="text-sm font-medium"
                    >
                        {{ stats.revenueGrowth >= 0 ? '+' : '' }}{{ stats.revenueGrowth }}%
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 ml-1.5">vs last month</span>
                </div>
            </div>

            <!-- Orders Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Orders</span>
                    <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.totalOrders }}</p>
                <div class="flex items-center mt-2 space-x-3">
                    <span class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">{{ stats.completedOrders }} completed</span>
                    <span class="text-sm text-amber-600 dark:text-amber-400 font-medium">{{ stats.pendingOrders }} pending</span>
                </div>
            </div>

            <!-- Users Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</span>
                    <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.totalUsers }}</p>
                <div class="flex items-center mt-2">
                    <span class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">+{{ stats.newUsersThisMonth }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 ml-1.5">this month</span>
                </div>
            </div>

            <!-- Products Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Products</span>
                    <div class="p-2 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.totalProducts }}</p>
                <div class="flex items-center mt-2">
                    <span class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">{{ stats.activeProducts }} active</span>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Revenue Chart -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Revenue Overview</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Last 30 days performance</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(stats.thisMonthRevenue) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">This month</p>
                    </div>
                </div>
                <div class="h-64">
                    <Line :data="revenueChartData" :options="revenueChartOptions" />
                </div>
            </div>

            <!-- User Distribution -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-6">Users by Role</h3>
                <div class="h-48 flex items-center justify-center">
                    <Doughnut :data="userRoleChartData" :options="userRoleChartOptions" />
                </div>
                <div class="mt-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Admin</span>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ userStats.byRole.admin }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Seller</span>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ userStats.byRole.seller }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-emerald-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Buyer</span>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ userStats.byRole.buyer }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Top Products -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Top Selling Products</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div v-for="(product, index) in topProducts.slice(0, 5)" :key="product.id" class="p-4 flex items-center space-x-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ index + 1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ product.name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ product.sales_count }} sales</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(product.total_revenue) }}</p>
                        </div>
                    </div>
                    <div v-if="topProducts.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No sales data yet
                    </div>
                </div>
            </div>

            <!-- Top Sellers -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Top Sellers</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div v-for="(seller, index) in topSellers.slice(0, 5)" :key="seller.id" class="p-4 flex items-center space-x-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-white">{{ seller.name.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ seller.name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ seller.products_count }} products, {{ seller.sales_count }} sales</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(seller.total_revenue) }}</p>
                        </div>
                    </div>
                    <div v-if="topSellers.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No seller data yet
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div v-for="order in recentOrders.slice(0, 5)" :key="order.id" class="p-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">#{{ order.order_number }}</span>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="getStatusColor(order.status)"
                            >
                                {{ order.status }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ order.buyer_name }}</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(order.total_amount) }}</span>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ order.created_at }}</p>
                    </div>
                    <div v-if="recentOrders.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No orders yet
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
