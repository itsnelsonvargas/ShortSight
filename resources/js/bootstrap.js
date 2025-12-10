/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Configure axios defaults for better error handling
window.axios.defaults.timeout = 10000; // 10 second timeout
window.axios.defaults.headers.common['Accept'] = 'application/json';

// Add response interceptor for global error handling
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        // Handle common error scenarios
        if (error.code === 'ECONNABORTED') {
            // Timeout error
            error.userMessage = 'Request timed out. Please check your connection and try again.';
        } else if (!error.response) {
            // Network error
            error.userMessage = 'Network error. Please check your internet connection.';
        } else if (error.response.status >= 500) {
            // Server error
            error.userMessage = 'Server error. Please try again later.';
        } else if (error.response.status === 429) {
            // Rate limited
            error.userMessage = 'Too many requests. Please wait a moment and try again.';
        }

        return Promise.reject(error);
    }
);

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
//     wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
