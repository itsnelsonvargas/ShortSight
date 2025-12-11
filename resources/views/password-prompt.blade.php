<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Password Protected Link') - ShortSight</title>
    <meta name="description" content="@yield('description', 'This link requires a password to access.')">

    <!-- SEO Meta Tags -->
    <meta name="robots" content="noindex, nofollow">

    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .password-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div id="app" class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="bg-white rounded-lg shadow-xl p-8">
                <!-- Header -->
                <div class="text-center">
                    <div class="mx-auto h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Password Protected Link</h1>
                    <p class="text-gray-600">This link requires a password to access.</p>
                </div>

                <!-- Password Form -->
                <form id="password-form" class="mt-8 space-y-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Enter Password
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                class="password-input appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm pr-10"
                                placeholder="Enter the password to access this link"
                            />
                            <button
                                type="button"
                                id="toggle-password"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center hover:text-gray-600"
                            >
                                <svg id="eye-closed" class="h-5 w-5 text-gray-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                </svg>
                                <svg id="eye-open" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div id="error-message" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm hidden">
                        <span id="error-text"></span>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        id="submit-btn"
                        class="btn-primary group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                    >
                        <span id="btn-text">Access Link</span>
                        <span id="btn-loading" class="hidden flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verifying...
                        </span>
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

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('password-form');
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('toggle-password');
            const eyeClosed = document.getElementById('eye-closed');
            const eyeOpen = document.getElementById('eye-open');
            const submitBtn = document.getElementById('submit-btn');
            const btnText = document.getElementById('btn-text');
            const btnLoading = document.getElementById('btn-loading');
            const errorMessage = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');

            let showPassword = false;

            // Toggle password visibility
            toggleBtn.addEventListener('click', function() {
                showPassword = !showPassword;
                passwordInput.type = showPassword ? 'text' : 'password';

                if (showPassword) {
                    eyeOpen.classList.add('hidden');
                    eyeClosed.classList.remove('hidden');
                } else {
                    eyeClosed.classList.add('hidden');
                    eyeOpen.classList.remove('hidden');
                }
            });

            // Form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const password = passwordInput.value.trim();
                if (!password) {
                    showError('Please enter a password.');
                    return;
                }

                setLoading(true);
                hideError();

                try {
                    const response = await fetch(`/api/links/{{ $slug }}/verify-password`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            password: password
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Redirect to the actual link
                        window.location.href = '/{{ $slug }}';
                    } else {
                        showError(data.message || 'Invalid password. Please try again.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showError('Network error. Please check your connection and try again.');
                } finally {
                    setLoading(false);
                }
            });

            function showError(message) {
                errorText.textContent = message;
                errorMessage.classList.remove('hidden');
            }

            function hideError() {
                errorMessage.classList.add('hidden');
            }

            function setLoading(loading) {
                submitBtn.disabled = loading;
                if (loading) {
                    btnText.classList.add('hidden');
                    btnLoading.classList.remove('hidden');
                } else {
                    btnLoading.classList.add('hidden');
                    btnText.classList.remove('hidden');
                }
            }
        });
    </script>
</body>
</html>
