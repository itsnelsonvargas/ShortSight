<template>
    <div v-cloak>
        <!-- Navbar -->
        <nav class="fixed w-full z-50 glass-nav border-b border-slate-200/50 transition-all duration-300 py-4">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <!-- Logo -->
                    <div class="flex items-center gap-2 cursor-pointer" @click="$router.push('/')">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                            <i class="ph ph-link text-xl font-bold"></i>
                        </div>
                        <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-800 to-slate-600">
                            Short<span class="text-indigo-600">Sight</span>
                        </span>
                    </div>

                    <!-- Desktop Nav -->
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="#features" class="text-slate-600 hover:text-indigo-600 font-medium transition-colors">Features</a>
                        <a href="#pricing" class="text-slate-600 hover:text-indigo-600 font-medium transition-colors">Pricing</a>

                        <div class="flex items-center gap-4">
                            <button @click="$router.push('/login')" class="text-slate-700 font-semibold hover:text-indigo-600">Log in</button>
                            <button @click="$router.push('/register')" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-all transform hover:-translate-y-0.5 hover:shadow-lg">
                                Sign Up Free
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-slate-700">
                        <i class="ph ph-list text-2xl"></i>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden min-h-screen flex items-center">
            <!-- Background Decor -->
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
                <div class="blob bg-purple-300 w-96 h-96 rounded-full top-0 left-0 -translate-x-1/2 -translate-y-1/2 opacity-30"></div>
                <div class="blob bg-indigo-300 w-[500px] h-[500px] rounded-full bottom-0 right-0 translate-x-1/3 translate-y-1/3 opacity-30 animation-delay-2000"></div>
            </div>

            <div class="container mx-auto px-4 relative z-10">
                <div class="max-w-md mx-auto">
                    <div class="text-center mb-8 animate-fade-in-up">
                        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight mb-4 leading-tight">
                            Join <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500">ShortSight</span>
                        </h1>
                        <p class="text-xl text-slate-600 leading-relaxed max-w-sm mx-auto">
                            Create your account and start shortening links in seconds.
                        </p>
                    </div>

                    <!-- Registration Card -->
                    <div class="animate-fade-in-up" style="animation-delay: 200ms;">
                        <div class="glass-panel rounded-3xl p-2 shadow-2xl shadow-indigo-500/10 transform transition-all duration-300 hover:shadow-indigo-500/20">
                            <div class="bg-white rounded-2xl p-8 md:p-10">

                                <!-- Error/Success Messages -->
                                <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm animate-fade-in-up">
                                    <div class="flex items-center gap-2">
                                        <i class="ph ph-warning-circle text-lg"></i>
                                        {{ errorMessage }}
                                    </div>
                                </div>

                                <div v-if="successMessage" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm animate-fade-in-up">
                                    <div class="flex items-center gap-2">
                                        <i class="ph ph-check-circle text-lg"></i>
                                        {{ successMessage }}
                                    </div>
                                </div>

                                <!-- Registration Form -->
                                <form @submit.prevent="register" class="space-y-5">

                                    <!-- Name Field -->
                                    <div>
                                        <label for="name" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider mb-3">
                                            Full Name
                                        </label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                                                <i class="ph ph-user text-2xl text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                            </div>
                                            <input
                                                type="text"
                                                id="name"
                                                v-model="form.name"
                                                placeholder="Enter your full name"
                                                required
                                                class="w-full pl-16 pr-6 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-xl text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all"
                                            >
                                        </div>
                                    </div>

                                    <!-- Email Field -->
                                    <div>
                                        <label for="email" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider mb-3">
                                            Email Address
                                        </label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                                                <i class="ph ph-envelope text-2xl text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                            </div>
                                            <input
                                                type="email"
                                                id="email"
                                                v-model="form.email"
                                                placeholder="Enter your email"
                                                required
                                                class="w-full pl-16 pr-6 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-xl text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all"
                                            >
                                        </div>
                                    </div>

                                    <!-- Password Field -->
                                    <div>
                                        <label for="password" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider mb-3">
                                            Password
                                        </label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                                                <i class="ph ph-lock text-2xl text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                            </div>
                                            <input
                                                type="password"
                                                id="password"
                                                v-model="form.password"
                                                placeholder="Create a strong password"
                                                required
                                                class="w-full pl-16 pr-6 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-xl text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all"
                                            >
                                        </div>
                                    </div>

                                    <!-- Confirm Password Field -->
                                    <div>
                                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider mb-3">
                                            Confirm Password
                                        </label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                                                <i class="ph ph-lock text-2xl text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                            </div>
                                            <input
                                                type="password"
                                                id="password_confirmation"
                                                v-model="form.password_confirmation"
                                                placeholder="Confirm your password"
                                                required
                                                class="w-full pl-16 pr-6 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-xl text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all"
                                            >
                                        </div>
                                    </div>

                                    <!-- Register Button -->
                                    <button
                                        type="submit"
                                        :disabled="isSubmitting"
                                        class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-lg rounded-xl transition-all transform active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-3 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/40"
                                    >
                                        <i v-if="isSubmitting" class="ph ph-spinner animate-spin text-xl"></i>
                                        <span v-else>🎉</span>
                                        {{ isSubmitting ? 'Creating your account...' : 'Create Free Account' }}
                                    </button>
                                </form>

                                <!-- Divider -->
                                <div class="my-8 text-center text-slate-400 relative">
                                    <span class="bg-white px-4 relative z-10 text-sm font-medium">or continue with</span>
                                    <hr class="border-slate-200 absolute top-1/2 left-0 right-0 -z-0" />
                                </div>

                                <!-- Social Login Buttons -->
                                <div class="space-y-3">
                                    <button
                                        @click="loginWithGoogle"
                                        class="w-full py-3.5 bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 rounded-xl font-bold text-base transition-all transform active:scale-95 flex items-center justify-center gap-3 shadow-lg hover:shadow-xl"
                                    >
                                        <svg class="w-6 h-6" viewBox="0 0 24 24">
                                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                        </svg>
                                        Continue with Google
                                    </button>

                                    <button
                                        @click="loginWithFacebook"
                                        class="w-full py-3.5 bg-[#1877F2] hover:bg-[#166FE5] text-white rounded-xl font-bold text-base transition-all transform active:scale-95 flex items-center justify-center gap-3 shadow-lg shadow-[#1877F2]/30"
                                    >
                                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                        Continue with Facebook
                                    </button>
                                </div>

                                <!-- Login Link -->
                                <div class="mt-6 text-center">
                                    <p class="text-slate-600 text-sm">
                                        Already have an account?
                                        <router-link to="/login" class="text-indigo-600 hover:text-indigo-700 font-semibold transition-colors">
                                            Sign in here
                                        </router-link>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const mobileMenuOpen = ref(false);

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const errorMessage = ref(null);
const successMessage = ref(null);
const isSubmitting = ref(false);

const register = async () => {
    isSubmitting.value = true;
    errorMessage.value = null;
    successMessage.value = null;

    try {
        const response = await window.axios.post('/api/register', form.value);
        successMessage.value = '🎉 Registration successful! Redirecting to login...';
        setTimeout(() => {
            window.location.href = '/login';
        }, 2000);
    } catch (error) {
        if (error.response?.data?.errors) {
            // Handle validation errors
            const errors = Object.values(error.response.data.errors).flat();
            errorMessage.value = errors.join(', ');
        } else {
            errorMessage.value = error.response?.data?.message || 'Registration failed. Please try again.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const loginWithGoogle = () => {
    window.location.href = '/auth/google';
};

const loginWithFacebook = () => {
    window.location.href = '/auth/facebook';
};
</script>

<style scoped>
/* Glassmorphism effect */
.glass-nav {
    backdrop-filter: blur(20px);
    background: rgba(255, 255, 255, 0.8);
}

.glass-panel {
    backdrop-filter: blur(20px);
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Animated background blobs */
.blob {
    position: absolute;
    border-radius: 50%;
    animation: blob 7s infinite;
}

.blob:nth-child(1) {
    animation-duration: 7s;
}

.blob:nth-child(2) {
    animation-duration: 9s;
    animation-delay: -2s;
}

@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

/* Animation classes */
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out forwards;
}

/* Hide scrollbar during animation */
[v-cloak] {
    display: none;
}
</style>
