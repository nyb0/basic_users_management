<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const props = defineProps({
    canLogin: {
        type: Boolean,
        required: false,
        default: false,
    },
    canRegister: {
        type: Boolean,
        required: false,
        default: false,
    },
    user: {
        type: Object,
        default: null,
    },
});

const showingNavigationDropdown = ref(false);

// Check if user is admin or moderator
const isAdminOrModerator = () => {
    return props.user?.isAdminOrModerator;
};

// Settings menu items for admin/moderator
const getSettingsMenuItems = () => {
    const items = [
        {
            label: 'Dashboard',
            href: route('dashboard'),
        },
        {
            label: 'Users',
            href: route('users.index'),
        },
    ];

    // Only admin can access Site Settings
    if (props.user?.isAdmin) {
        items.push({
            label: 'Site Settings',
            href: route('site-settings.index'),
        });
    }

    return items;
};

// User dropdown menu items based on role
const getUserMenuItems = () => {
    const user = props.user;

    if (!user) {
        return [];
    }

    // Regular user gets dashboard and profile
    if (!isAdminOrModerator()) {
        return [
            {
                label: 'Dashboard',
                href: route('dashboard'),
            },
            {
                label: 'Profile',
                href: route('profile.edit'),
            },
        ];
    }

    // Admin/moderator gets only profile in user dropdown
    return [
        {
            label: 'Profile',
            href: route('profile.edit'),
        },
    ];
};

// Handle mobile menu toggle
const toggleMobileMenu = () => {
    showingNavigationDropdown.value = !showingNavigationDropdown.value;
};

// Close mobile menu when clicking a link
const closeMobileMenu = () => {
    showingNavigationDropdown.value = false;
};
</script>

<template>
    <nav class="border-b border-gray-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <Link :href="route('welcome')">
                        <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                    </Link>
                </div>

                <!-- Right side navigation -->
                <div class="flex">
                    <div class="hidden sm:ms-6 sm:flex sm:items-center">
                        <template v-if="!props.user">
                            <!-- Guest navigation -->
                            <button
                                @click="$emit('open-login-modal')"
                                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                            >
                                Log in
                            </button>

                            <button
                                v-if="canRegister"
                                @click="$emit('open-register-modal')"
                                class="ml-4 rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                            >
                                Register
                            </button>
                        </template>

                        <template v-else>
                            <!-- Settings dropdown for admin/moderator -->
                            <div v-if="isAdminOrModerator()" class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                <svg
                                                    class="h-5 w-5"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <template v-for="item in getSettingsMenuItems()" :key="item.label">
                                            <DropdownLink :href="item.href">
                                                {{ item.label }}
                                            </DropdownLink>
                                        </template>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- User dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ props.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- User menu items based on role -->
                                        <template v-for="item in getUserMenuItems()" :key="item.label">
                                            <DropdownLink :href="item.href">
                                                {{ item.label }}
                                            </DropdownLink>
                                        </template>
                                        
                                        <!-- Logout -->
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </template>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button
                            @click="toggleMobileMenu"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                        >
                            <svg
                                class="h-6 w-6"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile navigation menu -->
        <div
            :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
            class="sm:hidden absolute w-full bg-white z-50 border-b border-gray-100"
        >
            <div class="space-y-1">
                <template v-if="!props.user">
                    <!-- Guest mobile navigation -->
                    <div class="px-4 py-2">
                        <button
                            @click="$emit('open-login-modal'); closeMobileMenu"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        >
                            Log in
                        </button>
                        <button
                            v-if="canRegister"
                            @click="$emit('open-register-modal'); closeMobileMenu"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        >
                            Register
                        </button>
                    </div>
                </template>

                <template v-else>
                    <div class="border-t border-b border-gray-200 pb-1 pt-4">
                        <!-- Settings section for admin/moderator -->
                        <template v-if="isAdminOrModerator()">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                Settings
                            </div>
                            <template v-for="item in getSettingsMenuItems()" :key="item.label">
                                <ResponsiveNavLink :href="item.href" @click="closeMobileMenu">
                                    {{ item.label }}
                                </ResponsiveNavLink>
                            </template>
                        </template>

                        <!-- User section -->
                        <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            {{ props.user.name }}
                        </div>
                        <template v-for="item in getUserMenuItems()" :key="item.label">
                            <ResponsiveNavLink :href="item.href" @click="closeMobileMenu">
                                {{ item.label }}
                            </ResponsiveNavLink>
                        </template>
                        
                        <!-- Logout -->
                        <ResponsiveNavLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                            @click="closeMobileMenu"
                        >
                            Log Out
                        </ResponsiveNavLink>
                    </div>
                </template>
            </div>
        </div>
    </nav>
</template>