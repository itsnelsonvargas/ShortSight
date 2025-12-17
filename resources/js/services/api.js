/**
 * API Service Layer with comprehensive error handling and retry logic
 */

class ApiService {
    constructor() {
        this.baseURL = '/api';
        this.maxRetries = 3;
        this.retryDelay = 1000; // Start with 1 second
    }

    /**
     * Generic API request with error handling and retry logic
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        let lastError;

        for (let attempt = 1; attempt <= this.maxRetries; attempt++) {
            try {
                const response = await window.axios({
                    url,
                    timeout: 10000,
                    ...options,
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers,
                    },
                });

                return response.data;
            } catch (error) {
                lastError = error;

                // Don't retry on client errors (4xx) except 429 (rate limit)
                if (error.response && error.response.status >= 400 && error.response.status < 500 && error.response.status !== 429) {
                    break;
                }

                // Don't retry on the last attempt
                if (attempt === this.maxRetries) {
                    break;
                }

                // Exponential backoff for retries
                const delay = this.retryDelay * Math.pow(2, attempt - 1);
                await this.sleep(delay);
            }
        }

        // Throw the last error with user-friendly message
        throw this.handleApiError(lastError);
    }

    /**
     * Handle API errors and convert to user-friendly messages
     */
    handleApiError(error) {
        if (error.userMessage) {
            // Already processed by interceptor
            return new Error(error.userMessage);
        }

        if (error.response) {
            const status = error.response.status;
            const data = error.response.data;

            switch (status) {
                case 400:
                    return new Error(data.message || 'Invalid request. Please check your input.');
                case 401:
                    return new Error('Authentication required. Please log in.');
                case 403:
                    return new Error('You don\'t have permission to perform this action.');
                case 404:
                    return new Error('The requested resource was not found.');
                case 422:
                    // Validation errors
                    if (data.errors) {
                        const firstError = Object.values(data.errors)[0];
                        return new Error(Array.isArray(firstError) ? firstError[0] : firstError);
                    }
                    return new Error(data.message || 'Validation failed. Please check your input.');
                case 429:
                    return new Error('Too many requests. Please wait a moment and try again.');
                case 500:
                    return new Error('Server error. Please try again later.');
                case 503:
                    return new Error('Service temporarily unavailable. Please try again later.');
                default:
                    return new Error(data.message || `Request failed with status ${status}`);
            }
        }

        if (error.code === 'ECONNABORTED') {
            return new Error('Request timed out. Please check your connection and try again.');
        }

        if (!navigator.onLine) {
            return new Error('No internet connection. Please check your network.');
        }

        return new Error('An unexpected error occurred. Please try again.');
    }

    /**
     * Sleep utility for retry delays
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Shorten URL endpoint
     */
    async shortenUrl(url, customSlug = null, recaptchaToken = null, password = null, expiresAt = null, autoDeleteExpired = false) {
        return this.request('/links', {
            method: 'POST',
            data: {
                url,
                customSlugInput: customSlug,
                customSlug: !!customSlug,
                recaptcha_token: recaptchaToken,
                password: password,
                expires_at: expiresAt,
                auto_delete_expired: autoDeleteExpired,
            },
        });
    }

    /**
     * Check if slug is available
     */
    async checkSlugAvailability(slug) {
        try {
            const response = await this.request(`/check-slug?slug=${encodeURIComponent(slug)}`);
            return response.available;
        } catch (error) {
            // If we can't check, assume it's not available to be safe
            console.warn('Could not check slug availability:', error.message);
            return false;
        }
    }

    /**
     * Get link analytics (placeholder for future implementation)
     */
    async getLinkAnalytics(slug) {
        return this.request(`/links/${slug}/analytics`);
    }

    /**
     * Get user links (placeholder for future implementation)
     */
    async getUserLinks() {
        return this.request('/user/links');
    }

    /**
     * Delete link (placeholder for future implementation)
     */
    async deleteLink(id) {
        return this.request(`/links/${id}`, {
            method: 'DELETE',
        });
    }

    /**
     * Create API token
     */
    async createToken(email, password) {
        return this.request('/token', {
            method: 'POST',
            data: { email, password },
        });
    }

    /**
     * Revoke API token
     */
    async revokeToken(tokenId) {
        return this.request('/token', {
            method: 'DELETE',
            data: { token: tokenId },
        });
    }
}

// Create singleton instance
const apiService = new ApiService();

export default apiService;
