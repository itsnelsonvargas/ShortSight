<template>
    <div>
        <Navbar />

        <div class="container mx-auto px-4 py-12">
            <!-- Hero Section -->
            <div class="text-center mb-12">
                <h1 class="text-5xl font-bold text-gray-800 mb-4">ShortSight</h1>
                <p class="text-xl text-gray-600">Fast, simple, and reliable URL shortening service</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
                <!-- URL Shortener Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold text-gray-800 flex items-center">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                                Shorten a URL
                            </h3>

                            <!-- Success Message -->
                            <div v-if="shortenedUrl" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <p class="text-gray-700 mb-2">Your shortened link is ready:</p>
                                <div class="flex items-center space-x-2">
                                    <a :href="shortenedUrl" target="_blank" class="text-blue-600 font-semibold hover:underline">
                                        {{ shortenedUrl }}
                                    </a>
                                    <button
                                        @click="copyToClipboard(shortenedUrl)"
                                        class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
                                    >
                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        Copy
                                    </button>
                                </div>
                            </div>

                            <!-- Error Message -->
                            <div v-if="errorMessage" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                                {{ errorMessage }}
                            </div>
                        </div>

                        <form @submit.prevent="shortenUrl">
                            <div class="mb-4">
                                <label for="url" class="block text-gray-700 font-medium mb-2">URL</label>
                                <input
                                    type="url"
                                    id="url"
                                    v-model="form.url"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="https://example.com"
                                    required
                                />
                            </div>

                            <div class="mb-4">
                                <div class="flex items-center mb-2">
                                    <input
                                        type="checkbox"
                                        id="customSlug"
                                        v-model="useCustomSlug"
                                        :disabled="!isAuthenticated"
                                        class="mr-2"
                                    />
                                    <label for="customSlug" class="text-gray-700">
                                        Use custom slug
                                        <span v-if="!isAuthenticated" class="text-sm text-gray-500">(Sign in required)</span>
                                    </label>
                                </div>

                                <input
                                    v-if="useCustomSlug && isAuthenticated"
                                    type="text"
                                    v-model="form.customSlug"
                                    @input="checkSlugAvailability"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Enter custom slug"
                                />
                                <small v-if="slugStatus" :class="slugStatusClass">{{ slugStatus }}</small>
                            </div>

                            <div v-if="!useCustomSlug" class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Slug Format:</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input
                                            type="radio"
                                            v-model="form.format"
                                            value="random7"
                                            class="mr-2"
                                        />
                                        <span>Random 7 characters <span class="text-gray-500 text-sm">(Ex: A1B2C3D)</span></span>
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            type="radio"
                                            v-model="form.format"
                                            value="random6Hyphen"
                                            class="mr-2"
                                        />
                                        <span>Random 6 characters with hyphen <span class="text-gray-500 text-sm">(Ex: ABC-123)</span></span>
                                    </label>
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ isSubmitting ? 'Shortening...' : 'Shorten' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Login/User Info Sidebar -->
                <div>
                    <div v-if="!isAuthenticated" class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Login</h3>
                        <p class="text-sm text-gray-600 mb-4">To track and manage your shortened links.</p>

                        <form @submit.prevent="login">
                            <div class="mb-4">
                                <label for="email" class="block text-gray-700 mb-2">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    v-model="loginForm.email"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>

                            <div class="mb-4">
                                <label for="password" class="block text-gray-700 mb-2">Password</label>
                                <input
                                    type="password"
                                    id="password"
                                    v-model="loginForm.password"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>

                            <button
                                type="submit"
                                class="w-full mb-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                Login
                            </button>

                            <router-link
                                to="/register"
                                class="block w-full text-center px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition"
                            >
                                Register
                            </router-link>
                        </form>

                        <div class="my-4 text-center text-gray-500">
                            <span class="bg-white px-2">or</span>
                            <hr class="border-gray-300 -mt-3 -z-10 relative" />
                        </div>

                        <button
                            @click="loginWithGoogle"
                            class="w-full mb-2 flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                        >
                            <img src="https://www.gstatic.com/marketing-cms/assets/images/d5/dc/cfe9ce8b4425b410b49b7f2dd3f3/g.webp=s96-fcrop64=1,00000000ffffffff-rw" class="w-5 h-5 mr-2" alt="Google" />
                            Sign in with Google
                        </button>

                        <button
                            @click="loginWithFacebook"
                            class="w-full flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                        >
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Sign in with Facebook
                        </button>
                    </div>

                    <div v-else class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Welcome!</h3>
                        <p class="text-gray-700 mb-1">{{ userName }}</p>
                        <p class="text-sm text-gray-500 mb-4">{{ userEmail }}</p>
                        <hr class="my-4" />
                        <button
                            @click="logout"
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pricing Section -->
            <div id="pricing" class="mt-16">
                <h3 class="text-3xl font-bold text-center text-gray-800 mb-8">Pricing</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                    <!-- Free Plan -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h5 class="text-xl font-bold text-center mb-4">No Account</h5>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Absolutely for free</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Unlimited link shortening</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span>User account not required</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                <span>No customization of slug</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                <span>No overview of visitors</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                <span>No ownership of the slug</span>
                            </li>
                        </ul>
                        <a href="#" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Start
                        </a>
                    </div>

                    <!-- User Account Plan -->
                    <div class="bg-white rounded-lg shadow-lg p-6 border-2 border-blue-600">
                        <h5 class="text-xl font-bold text-center mb-4">User Account</h5>
                        <p class="text-gray-600 mb-6">Create custom links and track your analytics</p>
                        <a href="#" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Get Started
                        </a>
                    </div>

                    <!-- Premium Plan -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h5 class="text-xl font-bold text-center mb-4">Premium Account</h5>
                        <p class="text-gray-600 mb-6">Advanced features and priority support</p>
                        <a href="#" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Coming Soon
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white mt-16 py-8">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <h4 class="font-bold mb-3">About us</h4>
                    </div>
                    <div>
                        <h4 class="font-bold mb-3">Contacts</h4>
                    </div>
                    <div>
                        <h4 class="font-bold mb-3">Support</h4>
                    </div>
                    <div>
                        <h4 class="font-bold mb-3">Comments and suggestions</h4>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<script>
import Navbar from '@/components/Navbar.vue';

export default {
    name: 'Home',
    components: {
        Navbar,
    },
    data() {
        return {
            form: {
                url: '',
                customSlug: '',
                format: 'random7',
            },
            loginForm: {
                email: '',
                password: '',
            },
            useCustomSlug: false,
            shortenedUrl: null,
            errorMessage: null,
            isSubmitting: false,
            isAuthenticated: false,
            userName: '',
            userEmail: '',
            slugStatus: '',
            slugStatusClass: '',
        };
    },
    methods: {
        async shortenUrl() {
            this.isSubmitting = true;
            this.errorMessage = null;
            this.shortenedUrl = null;

            try {
                const response = await window.axios.post('/api/links', {
                    url: this.form.url,
                    customSlug: this.useCustomSlug ? this.form.customSlug : null,
                    format: this.form.format,
                });

                this.shortenedUrl = response.data.short_url;
                this.form.url = '';
                this.form.customSlug = '';
            } catch (error) {
                this.errorMessage = error.response?.data?.message || 'An error occurred while shortening the URL.';
            } finally {
                this.isSubmitting = false;
            }
        },
        async checkSlugAvailability() {
            if (!this.form.customSlug) {
                this.slugStatus = '';
                return;
            }

            try {
                const response = await window.axios.get('/api/check-slug', {
                    params: { slug: this.form.customSlug }
                });

                if (response.data.available) {
                    this.slugStatus = 'Available!';
                    this.slugStatusClass = 'text-green-600';
                } else {
                    this.slugStatus = 'Already taken.';
                    this.slugStatusClass = 'text-red-600';
                }
            } catch (error) {
                this.slugStatus = '';
            }
        },
        copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            alert('Copied to clipboard!');
        },
        async login() {
            try {
                const response = await window.axios.post('/api/login', this.loginForm);
                this.isAuthenticated = true;
                this.userName = response.data.user.name;
                this.userEmail = response.data.user.email;
                this.loginForm = { email: '', password: '' };
            } catch (error) {
                alert('Login failed. Please check your credentials.');
            }
        },
        logout() {
            this.isAuthenticated = false;
            this.userName = '';
            this.userEmail = '';
        },
        loginWithGoogle() {
            window.location.href = '/auth/google';
        },
        loginWithFacebook() {
            window.location.href = '/auth/facebook';
        },
    },
};
</script>
