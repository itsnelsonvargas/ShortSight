<template>
    <div
        v-if="!consentGiven && !dismissed"
        class="fixed bottom-0 left-0 right-0 z-50 bg-gray-900 text-white p-4 shadow-lg border-t border-gray-700"
        role="dialog"
        aria-labelledby="cookie-title"
        aria-describedby="cookie-description"
    >
        <div class="container mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex-1">
                <h3 id="cookie-title" class="text-lg font-semibold mb-2">🍪 Cookie Preferences</h3>
                <p id="cookie-description" class="text-gray-300 text-sm leading-relaxed">
                    We use cookies to enhance your experience, analyze site usage, and provide personalized content.
                    By continuing to use our site, you agree to our use of cookies.
                    <router-link to="/privacy-policy" class="text-blue-400 hover:text-blue-300 underline ml-1">
                        Learn more
                    </router-link>
                </p>
            </div>

            <div class="flex gap-3 flex-shrink-0">
                <button
                    @click="acceptAll"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900"
                >
                    Accept All
                </button>

                <button
                    @click="acceptEssential"
                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-900"
                >
                    Essential Only
                </button>

                <button
                    @click="dismiss"
                    class="p-2 text-gray-400 hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-900 rounded"
                    aria-label="Dismiss cookie notice"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const consentGiven = ref(false);
const dismissed = ref(false);

// Check if user has already given consent
onMounted(() => {
    const cookieConsent = localStorage.getItem('cookie-consent');
    const cookieDismissed = localStorage.getItem('cookie-dismissed');

    if (cookieConsent) {
        consentGiven.value = true;
    }

    if (cookieDismissed) {
        dismissed.value = true;
    }
});

const acceptAll = () => {
    localStorage.setItem('cookie-consent', 'all');
    localStorage.setItem('cookie-consent-date', new Date().toISOString());
    consentGiven.value = true;

    // Enable analytics cookies if you have them
    enableAnalyticsCookies();
};

const acceptEssential = () => {
    localStorage.setItem('cookie-consent', 'essential');
    localStorage.setItem('cookie-consent-date', new Date().toISOString());
    consentGiven.value = true;

    // Disable non-essential cookies
    disableAnalyticsCookies();
};

const dismiss = () => {
    dismissed.value = true;
    localStorage.setItem('cookie-dismissed', 'true');
    localStorage.setItem('cookie-dismissed-date', new Date().toISOString());
};

const enableAnalyticsCookies = () => {
    // Enable Google Analytics or other tracking cookies
    // This is where you would initialize your analytics services
    console.log('Analytics cookies enabled');
};

const disableAnalyticsCookies = () => {
    // Disable non-essential cookies
    // Remove any existing analytics cookies
    console.log('Analytics cookies disabled');
};

// Expose functions for external use
defineExpose({
    resetConsent: () => {
        localStorage.removeItem('cookie-consent');
        localStorage.removeItem('cookie-consent-date');
        localStorage.removeItem('cookie-dismissed');
        localStorage.removeItem('cookie-dismissed-date');
        consentGiven.value = false;
        dismissed.value = false;
    },
    getConsentStatus: () => {
        return {
            consent: localStorage.getItem('cookie-consent'),
            dismissed: localStorage.getItem('cookie-dismissed'),
            date: localStorage.getItem('cookie-consent-date') || localStorage.getItem('cookie-dismissed-date')
        };
    }
});
</script>

<style scoped>
/* Additional styles if needed */
</style>
