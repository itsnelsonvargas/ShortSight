<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div class="bg-white rounded-lg shadow-xl p-8">
        <!-- Header -->
        <div class="text-center">
          <div class="mx-auto h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Password Protected Link</h2>
          <p class="text-gray-600">This link requires a password to access.</p>
        </div>

        <!-- Password Form -->
        <form @submit.prevent="submitPassword" class="mt-8 space-y-6">
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
              Enter Password
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="password"
                type="password"
                required
                class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm pr-10"
                placeholder="Enter the password to access this link"
                :disabled="isSubmitting"
              />
              <button
                type="button"
                @click="togglePasswordVisibility"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                :disabled="isSubmitting"
              >
                <svg
                  v-if="showPassword"
                  class="h-5 w-5 text-gray-400 hover:text-gray-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
                <svg
                  v-else
                  class="h-5 w-5 text-gray-400 hover:text-gray-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm">
            {{ errorMessage }}
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isSubmitting || !password.trim()"
            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            <span v-if="isSubmitting" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Verifying...
            </span>
            <span v-else>Access Link</span>
          </button>
        </form>

        <!-- Footer -->
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-500">
            This link is protected by ShortSight's premium password protection feature.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PasswordPrompt',
  data() {
    return {
      password: '',
      showPassword: false,
      isSubmitting: false,
      errorMessage: '',
      slug: this.$route.params.slug
    }
  },
  methods: {
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
      const passwordInput = document.getElementById('password');
      passwordInput.type = this.showPassword ? 'text' : 'password';
    },

    async submitPassword() {
      if (!this.password.trim()) {
        this.errorMessage = 'Please enter a password.';
        return;
      }

      this.isSubmitting = true;
      this.errorMessage = '';

      try {
        const response = await this.$api.post(`/api/links/${this.slug}/verify-password`, {
          password: this.password
        });

        if (response.data.success) {
          // Store the verified password in session storage for this slug
          sessionStorage.setItem(`link_password_${this.slug}`, this.password);

          // Redirect to the actual link
          window.location.href = `/${this.slug}`;
        } else {
          this.errorMessage = response.data.message || 'Invalid password. Please try again.';
        }
      } catch (error) {
        console.error('Password verification error:', error);

        if (error.response && error.response.data) {
          this.errorMessage = error.response.data.message || 'Failed to verify password. Please try again.';
        } else {
          this.errorMessage = 'Network error. Please check your connection and try again.';
        }
      } finally {
        this.isSubmitting = false;
      }
    }
  },

  mounted() {
    // Check if password was already verified in this session
    const storedPassword = sessionStorage.getItem(`link_password_${this.slug}`);
    if (storedPassword) {
      this.password = storedPassword;
      this.submitPassword();
    }
  }
}
</script>

<style scoped>
/* Additional custom styles if needed */
</style>
