<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    user: Object,
    roles: Array,
    recentOrders: Array,
    recentProducts: Array,
});

const showBanModal = ref(false);
const showRoleModal = ref(false);

const banForm = useForm({
    reason: '',
});

const roleForm = useForm({
    roles: [...props.user.roles],
});

const banUser = () => {
    banForm.post(route('admin.users.ban', props.user.id), {
        onSuccess: () => {
            showBanModal.value = false;
            banForm.reset();
        },
    });
};

const unbanUser = () => {
    useForm({}).post(route('admin.users.unban', props.user.id));
};

const updateRoles = () => {
    roleForm.put(route('admin.users.roles', props.user.id), {
        onSuccess: () => {
            showRoleModal.value = false;
        },
    });
};

const toggleRole = (role) => {
    const index = roleForm.roles.indexOf(role);
    if (index > -1) {
        roleForm.roles.splice(index, 1);
    } else {
        roleForm.roles.push(role);
    }
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        processing: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        paid: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        failed: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        cancelled: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
        active: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        banned: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        draft: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
        inactive: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    };
    return colors[status] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400';
};

const getRoleColor = (role) => {
    const colors = {
        admin: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        seller: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        buyer: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
    };
    return colors[role] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400';
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <Head :title="`User: ${user.name}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center space-x-3">
                <Link
                    :href="route('admin.users.index')"
                    class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ user.name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- User Info Card -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <div class="text-center mb-6">
                        <div class="h-20 w-20 mx-auto rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mb-4">
                            <span class="text-white font-bold text-2xl">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="getStatusColor(user.status)"
                            >
                                {{ user.status }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Roles</span>
                            <div class="flex flex-wrap gap-1 justify-end">
                                <span
                                    v-for="role in user.roles"
                                    :key="role"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                    :class="getRoleColor(role)"
                                >
                                    {{ role }}
                                </span>
                                <span v-if="user.roles.length === 0" class="text-sm text-gray-400 dark:text-gray-500">
                                    None
                                </span>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Joined</span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ user.created_at }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Email Verified</span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ user.email_verified_at || 'Not verified' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Orders</span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ user.orders_count }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Products</span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ user.products_count }}</span>
                        </div>

                        <!-- Ban info if banned -->
                        <div v-if="user.status === 'banned'" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <p class="text-sm font-medium text-red-800 dark:text-red-400">Banned on {{ user.banned_at }}</p>
                            <p class="text-sm text-red-600 dark:text-red-300 mt-1">{{ user.ban_reason }}</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 space-y-3">
                        <button
                            @click="showRoleModal = true"
                            class="w-full px-4 py-2.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
                        >
                            Edit Roles
                        </button>

                        <button
                            v-if="user.status === 'active'"
                            @click="showBanModal = true"
                            class="w-full px-4 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
                        >
                            Ban User
                        </button>

                        <button
                            v-else
                            @click="unbanUser"
                            class="w-full px-4 py-2.5 text-sm font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors"
                        >
                            Unban User
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activity -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Recent Orders -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
                    </div>
                    <div v-if="recentOrders.length > 0" class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div v-for="order in recentOrders" :key="order.id" class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">#{{ order.order_number }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ order.items_count }} items</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(order.total_amount) }}</p>
                                    <div class="flex space-x-2 mt-1">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                            :class="getStatusColor(order.payment_status)"
                                        >
                                            {{ order.payment_status }}
                                        </span>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                            :class="getStatusColor(order.status)"
                                        >
                                            {{ order.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">{{ order.created_at }}</p>
                        </div>
                    </div>
                    <div v-else class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No orders yet
                    </div>
                </div>

                <!-- Recent Products (if seller) -->
                <div v-if="user.roles.includes('seller') || user.roles.includes('admin')" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Recent Products</h3>
                    </div>
                    <div v-if="recentProducts.length > 0" class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div v-for="product in recentProducts" :key="product.id" class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ product.name }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ product.created_at }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(product.price) }}</p>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-1"
                                        :class="getStatusColor(product.status)"
                                    >
                                        {{ product.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No products yet
                    </div>
                </div>
            </div>
        </div>

        <!-- Ban Modal -->
        <div v-if="showBanModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80" @click="showBanModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ban User</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Are you sure you want to ban <strong class="text-gray-900 dark:text-white">{{ user.name }}</strong>? They will not be able to access the platform.
                    </p>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Reason for ban</label>
                        <textarea
                            v-model="banForm.reason"
                            rows="3"
                            class="w-full rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Enter reason for banning this user..."
                        ></textarea>
                        <p v-if="banForm.errors.reason" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ banForm.errors.reason }}</p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="showBanModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="banUser"
                            :disabled="banForm.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors"
                        >
                            Ban User
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Modal -->
        <div v-if="showRoleModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80" @click="showRoleModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Edit User Roles</h3>
                    <div class="space-y-3 mb-6">
                        <label
                            v-for="role in roles"
                            :key="role"
                            class="flex items-center p-3 rounded-lg border cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                            :class="roleForm.roles.includes(role) ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'border-gray-200 dark:border-gray-600'"
                        >
                            <input
                                type="checkbox"
                                :checked="roleForm.roles.includes(role)"
                                @change="toggleRole(role)"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded"
                            />
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">
                                {{ role.charAt(0).toUpperCase() + role.slice(1) }}
                            </span>
                        </label>
                    </div>
                    <p v-if="roleForm.errors.roles" class="mb-4 text-sm text-red-600 dark:text-red-400">{{ roleForm.errors.roles }}</p>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="showRoleModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="updateRoles"
                            :disabled="roleForm.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors"
                        >
                            Save Roles
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
