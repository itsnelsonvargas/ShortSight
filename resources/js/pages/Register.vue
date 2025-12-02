<template>
    <div>
        <Navbar />

        <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Register</h2>

                    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700">
                        {{ errorMessage }}
                    </div>

                    <div v-if="successMessage" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700">
                        {{ successMessage }}
                    </div>

                    <form @submit.prevent="register">
                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 font-medium mb-2">Name</label>
                            <input
                                type="text"
                                id="name"
                                v-model="form.name"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter your name"
                                required
                            />
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                            <input
                                type="email"
                                id="email"
                                v-model="form.email"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter your email"
                                required
                            />
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                            <input
                                type="password"
                                id="password"
                                v-model="form.password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter your password"
                                required
                            />
                        </div>

                        <div class="mb-6">
                            <label for="password_confirmation" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                            <input
                                type="password"
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Confirm your password"
                                required
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="w-full px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Creating account...' : 'Register' }}
                        </button>
                    </form>

                    <div class="my-6 text-center text-gray-500 relative">
                        <span class="bg-white px-4 relative z-10">or</span>
                        <hr class="border-gray-300 absolute top-1/2 left-0 right-0 -z-0" />
                    </div>

                    <button
                        @click="loginWithGoogle"
                        class="w-full mb-3 flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                    >
                        <img src="https://www.gstatic.com/marketing-cms/assets/images/d5/dc/cfe9ce8b4425b410b49b7f2dd3f3/g.webp=s96-fcrop64=1,00000000ffffffff-rw" class="w-5 h-5 mr-2" alt="Google" />
                        Sign up with Google
                    </button>

                    <button
                        @click="loginWithFacebook"
                        class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        Sign up with Facebook
                    </button>

                    <p class="mt-6 text-center text-gray-600">
                        Already have an account?
                        <router-link to="/login" class="text-blue-600 hover:underline">Login here</router-link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Navbar from '@/components/Navbar.vue';

export default {
    name: 'Register',
    components: {
        Navbar,
    },
    data() {
        return {
            form: {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
            },
            errorMessage: null,
            successMessage: null,
            isSubmitting: false,
        };
    },
    methods: {
        async register() {
            this.isSubmitting = true;
            this.errorMessage = null;
            this.successMessage = null;

            try {
                const response = await window.axios.post('/api/register', this.form);
                this.successMessage = 'Registration successful! Redirecting to login...';
                setTimeout(() => {
                    this.$router.push('/login');
                }, 2000);
            } catch (error) {
                this.errorMessage = error.response?.data?.message || 'Registration failed. Please try again.';
            } finally {
                this.isSubmitting = false;
            }
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
