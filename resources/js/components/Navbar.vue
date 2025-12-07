<template>
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <router-link to="/" class="text-2xl font-bold text-blue-600">
                    ShortSight
                </router-link>

                <div class="hidden md:flex space-x-6">
                    <router-link to="/" class="text-gray-700 hover:text-blue-600 transition">
                        Home
                    </router-link>
                    <a href="#features" class="text-gray-700 hover:text-blue-600 transition">
                        Features
                    </a>
                    <a href="#pricing" class="text-gray-700 hover:text-blue-600 transition">
                        Pricing
                    </a>
                    <a href="#contact" class="text-gray-700 hover:text-blue-600 transition">
                        Contact
                    </a>
                </div>

                <div class="flex items-center space-x-3">
                    <template v-if="!isAuthenticated">
                        <router-link
                            to="/login"
                            class="px-4 py-2 text-blue-600 hover:text-blue-700 transition"
                        >
                            Login
                        </router-link>
                        <router-link
                            to="/register"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                        >
                            Sign Up
                        </router-link>
                    </template>
                    <template v-else>
                        <span class="text-gray-700">Welcome, {{ userName }}</span>
                        <button
                            @click="logout"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                        >
                            Logout
                        </button>
                    </template>
                </div>

                <!-- Mobile menu button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile menu -->
            <div v-if="mobileMenuOpen" class="md:hidden pb-4">
                <router-link to="/" class="block py-2 text-gray-700 hover:text-blue-600">
                    Home
                </router-link>
                <a href="#features" class="block py-2 text-gray-700 hover:text-blue-600">
                    Features
                </a>
                <a href="#pricing" class="block py-2 text-gray-700 hover:text-blue-600">
                    Pricing
                </a>
                <a href="#contact" class="block py-2 text-gray-700 hover:text-blue-600">
                    Contact
                </a>
                <template v-if="!isAuthenticated">
                    <router-link to="/login" class="block py-2 text-gray-700 hover:text-blue-600">
                        Login
                    </router-link>
                    <router-link to="/register" class="block py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center mt-2">
                        Sign Up
                    </router-link>
                </template>
            </div>
        </div>
    </nav>
</template>

<script>
export default {
    name: 'Navbar',
    data() {
        return {
            mobileMenuOpen: false,
            isAuthenticated: false,
            userName: '',
        };
    },
    methods: {
        logout() {
            // Implement logout logic
            this.isAuthenticated = false;
            this.userName = '';
        },
    },
};
</script>
