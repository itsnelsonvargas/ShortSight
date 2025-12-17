<template>
  <div v-cloak>
    <!-- Navbar -->
    <nav
      class="fixed w-full z-50 glass-nav border-b border-slate-200/50 transition-all duration-300"
      :class="{'py-2': isScrolled, 'py-4': !isScrolled}"
    >
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <!-- Logo -->
          <div class="flex items-center gap-2 cursor-pointer" @click="resetForm">
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

            <div v-if="!isAuthenticated" class="flex items-center gap-4">
              <button @click="showLoginModal = true" class="text-slate-700 font-semibold hover:text-indigo-600">Log in</button>
              <button @click="$router.push('/register')" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-all transform hover:-translate-y-0.5 hover:shadow-lg">
                Sign Up Free
              </button>
            </div>
            <div v-else class="flex items-center gap-4">
              <div class="flex flex-col text-right mr-2">
                <span class="text-sm font-bold text-slate-800">{{ user.name }}</span>
                <span class="text-xs text-slate-500">Premium Plan</span>
              </div>
              <button @click="logout" class="text-slate-500 hover:text-red-500 transition-colors">
                <i class="ph ph-sign-out text-xl"></i>
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
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
      <!-- Background Decor -->
      <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
        <div class="blob bg-purple-300 w-96 h-96 rounded-full top-0 left-0 -translate-x-1/2 -translate-y-1/2 opacity-30"></div>
        <div class="blob bg-indigo-300 w-[500px] h-[500px] rounded-full bottom-0 right-0 translate-x-1/3 translate-y-1/3 opacity-30 animation-delay-2000"></div>
      </div>

      <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center mb-12">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-semibold mb-6 animate-fade-in-up">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
            </span>
            Now with advanced analytics v2.0
          </div>

          <h1 class="text-5xl md:text-7xl font-extrabold text-slate-900 tracking-tight mb-6 leading-tight animate-fade-in-up" style="animation-delay: 100ms;">
            Make every connection <br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500">count.</span>
          </h1>

          <p class="text-xl text-slate-600 mb-10 max-w-2xl mx-auto leading-relaxed animate-fade-in-up" style="animation-delay: 200ms;">
            Transform long, ugly links into powerful marketing assets. Track clicks, analyze data, and manage your brand.
          </p>
        </div>

        <!-- Main Shortener Card -->
        <div class="max-w-3xl mx-auto animate-fade-in-up" style="animation-delay: 300ms;">
          <div class="glass-panel rounded-3xl p-2 shadow-2xl shadow-indigo-500/10 transform transition-all duration-300 hover:shadow-indigo-500/20">
            <div class="bg-white rounded-2xl p-6 md:p-8">

              <!-- Input State -->
              <div v-if="!result" class="space-y-6">
                <!-- Loading Overlay -->
                <div v-if="loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm rounded-2xl flex items-center justify-center z-10">
                  <div class="text-center">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                      <i class="ph ph-spinner animate-spin text-2xl text-indigo-600"></i>
                    </div>
                    <p class="text-indigo-700 font-medium">Shortening your link...</p>
                    <p class="text-indigo-500 text-sm mt-1">Validating URL and generating slug</p>
                  </div>
                </div>

                <form @submit.prevent="shortenUrl" class="relative">
                  <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                      <i class="ph ph-link text-2xl text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input
                      type="url"
                      v-model="url"
                      placeholder="Paste your long link here..."
                      required
                      :class="[
                        'w-full pl-16 pr-40 py-6 bg-slate-50 border-2 rounded-xl text-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 transition-all',
                        error ? 'border-red-300 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-100 focus:border-indigo-500 focus:ring-indigo-500/10'
                      ]"
                    >
                    <button
                      type="submit"
                      :disabled="loading || !url"
                      class="absolute right-3 top-3 bottom-3 bg-indigo-600 hover:bg-indigo-700 text-white px-8 rounded-lg font-bold text-lg transition-all transform active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2 shadow-lg shadow-indigo-500/30"
                    >
                      <i v-if="loading" class="ph ph-spinner animate-spin text-xl"></i>
                      <span v-if="loading" class="text-sm">Processing...</span>
                      <span v-else>Shorten</span>
                    </button>
                  </div>

                  <!-- Error Messages -->
                  <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start gap-3">
                      <i class="ph ph-warning-circle text-red-500 text-lg mt-0.5"></i>
                      <div>
                        <p class="text-red-800 font-medium">Unable to shorten URL</p>
                        <p class="text-red-700 text-sm mt-1">{{ error }}</p>
                      </div>
                    </div>
                  </div>
                </form>

                <!-- Advanced Options Toggle -->
                <div class="flex items-center justify-between pt-2">
                  <button @click="showOptions = !showOptions" class="flex items-center gap-2 text-slate-500 hover:text-indigo-600 font-medium text-sm transition-colors">
                    <i class="ph" :class="showOptions ? 'ph-caret-up' : 'ph-caret-down'"></i>
                    {{ showOptions ? 'Hide Options' : 'Custom Alias & Options' }}
                  </button>
                  <span class="text-xs text-slate-400 flex items-center gap-1">
                    <i class="ph ph-lock-key"></i> HTTPS Secured
                  </span>
                </div>

                <!-- Advanced Options Panel -->
                <div v-if="showOptions" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                  <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Custom Alias</label>
                    <div class="flex">
                      <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-slate-200 bg-slate-50 text-slate-500 text-sm">short.sight/</span>
                      <input
                        v-model="customSlug"
                        type="text"
                        placeholder="my-link"
                        :class="[
                          'flex-1 min-w-0 block w-full px-3 py-2 rounded-r-lg border text-sm focus:ring-indigo-500 focus:border-indigo-500',
                          slugError ? 'border-red-300 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200'
                        ]"
                      >
                    </div>
                    <!-- Slug Error Message -->
                    <div v-if="slugError" class="mt-2 p-2 bg-red-50 border border-red-200 rounded text-sm">
                      <p class="text-red-700">{{ slugError }}</p>
                    </div>
                  </div>
                  <div :class="{'opacity-50 pointer-events-none': !isAuthenticated}">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 flex justify-between">
                      Expiration Date
                      <span v-if="!isAuthenticated" class="text-indigo-500 text-[10px] bg-indigo-50 px-2 py-0.5 rounded-full">PRO</span>
                    </label>
                    <input
                      v-model="expiresAt"
                      type="datetime-local"
                      :disabled="!isAuthenticated"
                      class="block w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 text-slate-900 disabled:text-slate-500"
                      :min="minExpirationDate"
                    >
                    <div class="mt-2 flex items-center gap-2">
                      <input
                        v-model="autoDeleteExpired"
                        type="checkbox"
                        :disabled="!isAuthenticated"
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                      >
                      <label class="text-xs text-slate-600">Auto-delete when expired</label>
                    </div>
                  </div>
                  <div :class="{'opacity-50 pointer-events-none': !isAuthenticated}">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 flex justify-between">
                      Password Protection
                      <span v-if="!isAuthenticated" class="text-indigo-500 text-[10px] bg-indigo-50 px-2 py-0.5 rounded-full">PREMIUM</span>
                    </label>
                    <div class="relative">
                      <input
                        v-model="linkPassword"
                        type="password"
                        placeholder="Set password (optional)"
                        :disabled="!isAuthenticated"
                        class="block w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 pr-10"
                      >
                      <button
                        type="button"
                        @click="togglePasswordVisibility"
                        :disabled="!isAuthenticated"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                      >
                        <i class="ph" :class="showPasswordField ? 'ph-eye-slash' : 'ph-eye'"></i>
                      </button>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Require password to access this link</p>
                  </div>
                </div>
              </div>

              <!-- Success Result State -->
              <div v-else class="text-center py-4">
                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
                  <i class="ph ph-check-fat text-3xl font-bold"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Link ready!</h3>
                <p class="text-slate-500 mb-8">Your long URL has been successfully shortened.</p>

                <!-- Short URL Card -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-2 flex flex-col md:flex-row items-center gap-2 mb-6">
                  <div class="flex-1 text-left px-4 py-2 truncate w-full">
                    <a :href="result.shortUrl" target="_blank" class="text-indigo-600 font-bold text-xl hover:underline truncate block">
                      {{ result.shortUrl }}
                    </a>
                    <span class="text-xs text-slate-400 truncate block">{{ result.originalUrl }}</span>
                  </div>
                  <div class="flex gap-2 w-full md:w-auto px-2">
                    <button @click="copyToClipboard" class="flex-1 md:flex-none py-3 px-6 bg-slate-900 text-white rounded-lg font-medium hover:bg-slate-800 transition-colors flex items-center justify-center gap-2 min-w-[120px]">
                      <i class="ph" :class="copied ? 'ph-check' : 'ph-copy'"></i>
                      {{ copied ? 'Copied!' : 'Copy' }}
                    </button>
                    <button class="p-3 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-100">
                      <i class="ph ph-share-network text-xl"></i>
                    </button>
                  </div>
                </div>

                <!-- QR Code Section -->
                <div v-if="qrCodeUrl" class="bg-white border-2 border-indigo-100 rounded-2xl p-6 mb-6">
                  <div class="flex flex-col items-center">
                    <h4 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                      <i class="ph ph-qr-code text-indigo-600 text-2xl"></i>
                      QR Code
                    </h4>
                    <div class="bg-white p-4 rounded-xl shadow-lg mb-4">
                      <img :src="qrCodeUrl" alt="QR Code" class="w-48 h-48 mx-auto" />
                    </div>
                    <p class="text-sm text-slate-500 mb-4">Scan to access your short link</p>
                    <button @click="downloadQRCode" class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-colors flex items-center gap-2 shadow-lg shadow-indigo-500/30">
                      <i class="ph ph-download text-xl"></i>
                      Download QR Code (PNG)
                    </button>
                  </div>
                </div>

                <button @click="resetForm" class="text-sm text-slate-500 hover:text-indigo-600 font-medium underline decoration-2 decoration-transparent hover:decoration-indigo-600 transition-all">
                  Shorten another link
                </button>
              </div>

            </div>

            <!-- Quick Stats/Social Proof under card -->
            <div class="px-8 py-4 bg-slate-50/50 rounded-b-3xl border-t border-slate-100 flex justify-between items-center text-sm text-slate-500">
              <div class="flex items-center gap-1">
                <i class="ph ph-shield-check text-green-500 text-lg"></i>
                <span>Malware Protection</span>
              </div>
              <div class="hidden md:flex items-center gap-1">
                <i class="ph ph-lightning text-yellow-500 text-lg"></i>
                <span>99.9% Uptime</span>
              </div>
              <div>
                Used by <span class="font-bold text-slate-700">10k+</span> creators
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Links History -->
        <div v-if="history.length > 0" class="max-w-3xl mx-auto mt-12 animate-fade-in-up" style="animation-delay: 400ms;">
          <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 pl-2">Recent Links</h3>
          <div class="bg-white rounded-2xl shadow-lg shadow-slate-200/50 overflow-hidden divide-y divide-slate-100 border border-slate-100">
            <div v-for="(item, index) in history" :key="index" class="p-4 hover:bg-slate-50 transition-colors flex items-center justify-between group">
              <div class="flex items-center gap-4 overflow-hidden">
                <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                  <i class="ph ph-link-simple"></i>
                </div>
                <div class="min-w-0">
                  <p class="text-indigo-600 font-semibold truncate">{{ item.shortUrl }}</p>
                  <p class="text-slate-400 text-xs truncate max-w-[200px] md:max-w-md">{{ item.originalUrl }}</p>
                </div>
              </div>
              <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <span class="text-xs text-slate-400 mr-2">{{ item.clicks }} clicks</span>
                <button @click="copyHistoryItem(item.shortUrl)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">
                  <i class="ph ph-copy"></i>
                </button>
                <button class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">
                  <i class="ph ph-chart-bar"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Grid -->
    <section id="features" class="py-20 bg-white">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold text-slate-900 mb-4">Why choose ShortSight?</h2>
          <p class="text-slate-600 max-w-2xl mx-auto">We provide the tools you need to grow your brand and understand your audience.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
          <!-- Feature 1 -->
          <div class="group p-8 rounded-3xl bg-slate-50 hover:bg-gradient-to-br hover:from-indigo-50 hover:to-white transition-all duration-300 hover:shadow-xl border border-slate-100">
            <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <i class="ph ph-lightning text-3xl text-indigo-600"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">Lightning Fast</h3>
            <p class="text-slate-600 leading-relaxed">Redirects happen in milliseconds. Our global CDN ensures your links work instantly, everywhere.</p>
          </div>

          <!-- Feature 2 -->
          <div class="group p-8 rounded-3xl bg-slate-50 hover:bg-gradient-to-br hover:from-purple-50 hover:to-white transition-all duration-300 hover:shadow-xl border border-slate-100">
            <div class="w-14 h-14 bg-purple-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <i class="ph ph-chart-line-up text-3xl text-purple-600"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">Detailed Analytics</h3>
            <p class="text-slate-600 leading-relaxed">Know your audience. Track clicks, geographic location, referrers, and device types in real-time.</p>
          </div>

          <!-- Feature 3 -->
          <div class="group p-8 rounded-3xl bg-slate-50 hover:bg-gradient-to-br hover:from-pink-50 hover:to-white transition-all duration-300 hover:shadow-xl border border-slate-100">
            <div class="w-14 h-14 bg-pink-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <i class="ph ph-link text-3xl text-pink-600"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">Custom Aliases</h3>
            <p class="text-slate-600 leading-relaxed">Ditch the random characters. Create memorable, branded links that increase click-through rates.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Comparison Table -->
    <section class="py-20 bg-slate-50">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold text-slate-900 mb-4">Feature Comparison</h2>
          <p class="text-slate-600 max-w-2xl mx-auto">See how ShortSight compares to the competition.</p>
        </div>

        <div class="max-w-6xl mx-auto">
          <div class="bg-white rounded-2xl shadow-lg shadow-slate-200/50 overflow-hidden border border-slate-200">
            <!-- Table Content -->
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                  <tr>
                    <th class="text-left py-6 px-6 font-semibold text-slate-700 text-sm uppercase tracking-wider">Feature</th>
                    <th class="text-center py-6 px-4 font-semibold text-slate-700">ShortSight</th>
                    <th class="text-center py-6 px-4 font-semibold text-slate-600">bit.ly</th>
                    <th class="text-center py-6 px-4 font-semibold text-slate-600">TinyURL</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Speed -->
                  <tr class="border-b border-slate-100 hover:bg-slate-50/30 transition-colors">
                    <td class="py-5 px-6 font-medium text-slate-900">
                      <div class="flex items-center gap-3">
                        <i class="ph ph-lightning text-slate-400 text-lg"></i>
                        Speed & Performance
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex flex-col items-center gap-1">
                        <div class="flex items-center gap-1 text-green-600 font-medium">
                          <i class="ph ph-check text-lg"></i>
                          <span class="text-sm">Lightning Fast</span>
                        </div>
                        <span class="text-xs text-slate-500">Global CDN</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-600 font-medium">
                        <i class="ph ph-check text-lg"></i>
                        <span class="text-sm">Good</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-600 font-medium">
                        <i class="ph ph-check text-lg"></i>
                        <span class="text-sm">Average</span>
                      </div>
                    </td>
                  </tr>

                  <!-- Security -->
                  <tr class="border-b border-slate-100 hover:bg-slate-50/30 transition-colors">
                    <td class="py-5 px-6 font-medium text-slate-900">
                      <div class="flex items-center gap-3">
                        <i class="ph ph-shield-check text-slate-400 text-lg"></i>
                        Security & Safety
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex flex-col items-center gap-1">
                        <div class="flex items-center gap-1 text-green-600 font-medium">
                          <i class="ph ph-check text-lg"></i>
                          <span class="text-sm">Advanced</span>
                        </div>
                        <span class="text-xs text-slate-500">Malware protection + HTTPS</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-600 font-medium">
                        <i class="ph ph-check text-lg"></i>
                        <span class="text-sm">Basic</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-500 font-medium">
                        <i class="ph ph-minus text-lg"></i>
                        <span class="text-sm">Limited</span>
                      </div>
                    </td>
                  </tr>

                  <!-- Analytics -->
                  <tr class="border-b border-slate-100 hover:bg-slate-50/30 transition-colors">
                    <td class="py-5 px-6 font-medium text-slate-900">
                      <div class="flex items-center gap-3">
                        <i class="ph ph-chart-line-up text-slate-400 text-lg"></i>
                        Analytics & Insights
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex flex-col items-center gap-1">
                        <div class="flex items-center gap-1 text-green-600 font-medium">
                          <i class="ph ph-check text-lg"></i>
                          <span class="text-sm">Comprehensive</span>
                        </div>
                        <span class="text-xs text-slate-500">Real-time + Geo/Device data</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-600 font-medium">
                        <i class="ph ph-check text-lg"></i>
                        <span class="text-sm">Advanced</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-red-500 font-medium">
                        <i class="ph ph-x text-lg"></i>
                        <span class="text-sm">None</span>
                      </div>
                    </td>
                  </tr>

                  <!-- Custom Aliases -->
                  <tr class="border-b border-slate-100 hover:bg-slate-50/30 transition-colors">
                    <td class="py-5 px-6 font-medium text-slate-900">
                      <div class="flex items-center gap-3">
                        <i class="ph ph-link text-slate-400 text-lg"></i>
                        Custom Aliases
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex flex-col items-center gap-1">
                        <div class="flex items-center gap-1 text-green-600 font-medium">
                          <i class="ph ph-check text-lg"></i>
                          <span class="text-sm">Unlimited</span>
                        </div>
                        <span class="text-xs text-slate-500">Branded & memorable</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-600 font-medium">
                        <i class="ph ph-check text-lg"></i>
                        <span class="text-sm">Paid plans</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-red-500 font-medium">
                        <i class="ph ph-x text-lg"></i>
                        <span class="text-sm">Not available</span>
                      </div>
                    </td>
                  </tr>

                  <!-- QR Codes -->
                  <tr class="border-b border-slate-100 hover:bg-slate-50/30 transition-colors">
                    <td class="py-5 px-6 font-medium text-slate-900">
                      <div class="flex items-center gap-3">
                        <i class="ph ph-qr-code text-slate-400 text-lg"></i>
                        QR Code Generation
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex flex-col items-center gap-1">
                        <div class="flex items-center gap-1 text-green-600 font-medium">
                          <i class="ph ph-check text-lg"></i>
                          <span class="text-sm">Instant</span>
                        </div>
                        <span class="text-xs text-slate-500">Auto-generated + Downloadable</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-600 font-medium">
                        <i class="ph ph-check text-lg"></i>
                        <span class="text-sm">Available</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-red-500 font-medium">
                        <i class="ph ph-x text-lg"></i>
                        <span class="text-sm">Not available</span>
                      </div>
                    </td>
                  </tr>

                  <!-- API Access -->
                  <tr class="border-b border-slate-100 hover:bg-slate-50/30 transition-colors">
                    <td class="py-5 px-6 font-medium text-slate-900">
                      <div class="flex items-center gap-3">
                        <i class="ph ph-code text-slate-400 text-lg"></i>
                        API Access
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex flex-col items-center gap-1">
                        <div class="flex items-center gap-1 text-green-600 font-medium">
                          <i class="ph ph-check text-lg"></i>
                          <span class="text-sm">Full REST API</span>
                        </div>
                        <span class="text-xs text-slate-500">Pro plan</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-600 font-medium">
                        <i class="ph ph-check text-lg"></i>
                        <span class="text-sm">Available</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-500 font-medium">
                        <i class="ph ph-minus text-lg"></i>
                        <span class="text-sm">Limited</span>
                      </div>
                    </td>
                  </tr>

                  <!-- Pricing -->
                  <tr class="border-b border-slate-100 hover:bg-slate-50/30 transition-colors">
                    <td class="py-5 px-6 font-medium text-slate-900">
                      <div class="flex items-center gap-3">
                        <i class="ph ph-money text-slate-400 text-lg"></i>
                        Pricing
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex flex-col items-center gap-1">
                        <div class="flex items-center gap-1 text-green-600 font-medium">
                          <i class="ph ph-check text-lg"></i>
                          <span class="text-sm">$0 - $12/mo</span>
                        </div>
                        <span class="text-xs text-slate-500">Free forever plan</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-600 font-medium">
                        <i class="ph ph-check text-lg"></i>
                        <span class="text-sm">$29+/mo</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-600 font-medium">
                        <i class="ph ph-check text-lg"></i>
                        <span class="text-sm">Free</span>
                      </div>
                    </td>
                  </tr>

                  <!-- UI/UX -->
                  <tr class="border-b border-slate-100 hover:bg-slate-50/30 transition-colors">
                    <td class="py-5 px-6 font-medium text-slate-900">
                      <div class="flex items-center gap-3">
                        <i class="ph ph-palette text-slate-400 text-lg"></i>
                        User Interface
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex flex-col items-center gap-1">
                        <div class="flex items-center gap-1 text-green-600 font-medium">
                          <i class="ph ph-check text-lg"></i>
                          <span class="text-sm">Modern & Intuitive</span>
                        </div>
                        <span class="text-xs text-slate-500">Glassmorphism design</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-600 font-medium">
                        <i class="ph ph-check text-lg"></i>
                        <span class="text-sm">Functional</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-500 font-medium">
                        <i class="ph ph-minus text-lg"></i>
                        <span class="text-sm">Basic</span>
                      </div>
                    </td>
                  </tr>

                  <!-- Uptime -->
                  <tr class="hover:bg-slate-50/30 transition-colors">
                    <td class="py-5 px-6 font-medium text-slate-900">
                      <div class="flex items-center gap-3">
                        <i class="ph ph-activity text-slate-400 text-lg"></i>
                        Uptime Reliability
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex flex-col items-center gap-1">
                        <div class="flex items-center gap-1 text-green-600 font-medium">
                          <i class="ph ph-check text-lg"></i>
                          <span class="text-sm">99.9%</span>
                        </div>
                        <span class="text-xs text-slate-500">SLA guaranteed</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-600 font-medium">
                        <i class="ph ph-check text-lg"></i>
                        <span class="text-sm">99.9%</span>
                      </div>
                    </td>
                    <td class="text-center py-5 px-4">
                      <div class="flex items-center gap-1 text-slate-500 font-medium">
                        <i class="ph ph-minus text-lg"></i>
                        <span class="text-sm">Not specified</span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Call to Action -->
            <div class="bg-slate-50 p-8 text-center border-t border-slate-200">
              <p class="text-slate-600 mb-4">Join thousands of creators who trust ShortSight for their link shortening needs.</p>
              <button @click="$router.push('/register')" class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
                Sign Up Free Today
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing (Simplified) -->
    <section id="pricing" class="py-20 bg-slate-50 border-t border-slate-200">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-slate-900 mb-12">Simple, transparent pricing</h2>
        <div class="flex flex-col md:flex-row justify-center gap-8 max-w-4xl mx-auto">
          <!-- Free -->
          <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 flex-1">
            <h3 class="text-xl font-bold text-slate-900">Basic</h3>
            <div class="text-4xl font-extrabold text-slate-900 my-4">$0</div>
            <ul class="space-y-3 text-slate-600 mb-8 text-left pl-4">
              <li class="flex items-center gap-2"><i class="ph ph-check text-green-500"></i> Unlimited Links</li>
              <li class="flex items-center gap-2"><i class="ph ph-check text-green-500"></i> Basic Analytics</li>
              <li class="flex items-center gap-2"><i class="ph ph-check text-green-500"></i> Ad-supported</li>
            </ul>
            <button @click="$router.push('/register')" class="w-full py-3 rounded-xl border-2 border-slate-900 text-slate-900 font-bold hover:bg-slate-50 transition-colors">Start Free</button>
          </div>
          <!-- Pro -->
          <div class="bg-indigo-600 p-8 rounded-3xl shadow-xl shadow-indigo-500/30 flex-1 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 bg-yellow-400 text-xs text-yellow-900 font-bold px-3 py-1 rounded-bl-xl">POPULAR</div>
            <h3 class="text-xl font-bold">Pro</h3>
            <div class="text-4xl font-extrabold my-4">$12<span class="text-lg font-medium opacity-70">/mo</span></div>
            <ul class="space-y-3 text-indigo-100 mb-8 text-left pl-4">
              <li class="flex items-center gap-2"><i class="ph ph-check text-white"></i> Custom Slugs</li>
              <li class="flex items-center gap-2"><i class="ph ph-check text-white"></i> No Ads</li>
              <li class="flex items-center gap-2"><i class="ph ph-check text-white"></i> API Access</li>
            </ul>
            <button @click="$router.push('/register')" class="w-full py-3 rounded-xl bg-white text-indigo-600 font-bold hover:bg-indigo-50 transition-colors">Get Pro</button>
          </div>
        </div>
      </div>
    </section>

    <!-- Developer Section -->
    <section class="py-8 bg-slate-50 border-t border-slate-200">
      <div class="container mx-auto px-4">
        <div class="text-center">
          <p class="text-sm text-slate-500">
            Crafted with <span class="text-red-500">♥</span> by
            <a href="#" class="text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
              Nelson
            </a>
            <span class="mx-2">•</span>
            <a href="https://github.com/yourusername/shortsight" target="_blank" class="text-slate-400 hover:text-slate-600 transition-colors text-xs">
              <i class="ph ph-linkedin-logo"></i> View Source
            </a>
          </p>
        </div>
      </div>
    </section>

    <!-- Footer --> 
    <footer class="bg-white border-t border-slate-200 py-12">
      <div class="container mx-auto px-4 text-center">
        <div class="flex items-center justify-center gap-2 mb-4 opacity-50">
          <i class="ph ph-link text-2xl"></i>
          <span class="font-bold text-xl">ShortSight</span>
        </div>
        <p class="text-slate-500 mb-8">© 2024 ShortSight Inc. All rights reserved.</p>
        <div class="flex justify-center gap-6 text-slate-400">
          <a href="#" class="hover:text-indigo-600 transition-colors"><i class="ph ph-twitter-logo text-2xl"></i></a>
          <a href="#" class="hover:text-indigo-600 transition-colors"><i class="ph ph-github-logo text-2xl"></i></a>
          <a href="#" class="hover:text-indigo-600 transition-colors"><i class="ph ph-linkedin-logo text-2xl"></i></a>
        </div>
      </div>
    </footer>

    <!-- Login Modal -->
    <div v-if="showLoginModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showLoginModal = false"></div>
      <div class="bg-white rounded-3xl p-8 max-w-md w-full relative z-10 shadow-2xl animate-fade-in-up">
        <button @click="showLoginModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
          <i class="ph ph-x text-2xl"></i>
        </button>
        <h3 class="text-2xl font-bold text-slate-900 mb-2">Welcome Back</h3>
        <p class="text-slate-500 mb-6">Sign in to manage your links and view analytics.</p>
        <button @click="simulateLogin" class="w-full py-3 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-colors mb-4 flex justify-center items-center gap-2">
          <i class="ph ph-google-logo text-lg"></i> Continue with Google
        </button>
        <div class="text-center text-sm text-slate-400">Mock Login - Click to authenticate</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import confetti from 'canvas-confetti';
import QRCode from 'qrcode';
import apiService from '../services/api';

const url = ref('');
const customSlug = ref('');
const linkPassword = ref('');
const expiresAt = ref('');
const autoDeleteExpired = ref(false);
const loading = ref(false);
const result = ref(null);
const copied = ref(false);
const showOptions = ref(false);
const isScrolled = ref(false);
const showLoginModal = ref(false);
const mobileMenuOpen = ref(false);
const qrCodeUrl = ref('');
const showPasswordField = ref(false);

// Error handling
const error = ref(null);
const slugError = ref(null);

// Mock Auth State (will be replaced with real auth later)
const isAuthenticated = ref(true);
const user = ref({ name: 'Alex Doe' });

// History stored in LocalStorage
const history = ref([]);

// Computed property for minimum expiration date (1 hour from now)
const minExpirationDate = computed(() => {
  const now = new Date();
  now.setHours(now.getHours() + 1);
  return now.toISOString().slice(0, 16); // Format for datetime-local input
});

/**
 * Execute Google reCAPTCHA v3
 */
const executeRecaptcha = (action) => {
  return new Promise((resolve, reject) => {
    if (!window.grecaptcha) {
      reject(new Error('reCAPTCHA not loaded'));
      return;
    }

    window.grecaptcha.ready(() => {
      window.grecaptcha.execute('{{ config("services.recaptcha.site_key") }}', { action })
        .then((token) => {
          resolve(token);
        })
        .catch(reject);
    });
  });
};

const handleScroll = () => {
  isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
  const saved = localStorage.getItem('shortsight_history');
  if (saved) history.value = JSON.parse(saved);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

const simulateLogin = () => {
  loading.value = true;
  setTimeout(() => {
    isAuthenticated.value = true;
    showLoginModal.value = false;
    loading.value = false;
    // Redirect to dashboard after login
    window.location.href = '/dashboard';
  }, 1000);
};

const logout = () => {
  isAuthenticated.value = false;
};

const togglePasswordVisibility = () => {
  showPasswordField.value = !showPasswordField.value;
  const passwordInput = document.querySelector('input[placeholder="Set password (optional)"]');
  if (passwordInput) {
    passwordInput.type = showPasswordField.value ? 'text' : 'password';
  }
};

const shortenUrl = async () => {
  if (!url.value) return;

  // Clear previous errors
  error.value = null;
  slugError.value = null;

  loading.value = true;

  try {
    // Check slug availability if custom slug provided
    if (customSlug.value) {
      const isAvailable = await apiService.checkSlugAvailability(customSlug.value);
      if (!isAvailable) {
        slugError.value = `The slug "${customSlug.value}" is already taken. Please choose a different one.`;
        loading.value = false;
        return;
      }
    }

    // Execute reCAPTCHA v3 for anonymous link creation
    let recaptchaToken = null;
    try {
      recaptchaToken = await executeRecaptcha('shorten_url');
    } catch (recaptchaError) {
      console.warn('reCAPTCHA failed:', recaptchaError);
      // Continue without reCAPTCHA token - backend will handle this
    }

    // Call the real API to shorten the URL with reCAPTCHA token, password, and expiration
    const response = await apiService.shortenUrl(
      url.value,
      customSlug.value,
      recaptchaToken,
      linkPassword.value,
      expiresAt.value,
      autoDeleteExpired.value
    );

    const shortUrl = response.short_url || `short.sight/${response.slug}`;
    const newLink = {
      originalUrl: url.value,
      shortUrl: shortUrl,
      clicks: 0,
      date: new Date().toLocaleDateString(),
    };

    result.value = newLink;

    // Generate QR Code automatically
    try {
      qrCodeUrl.value = await QRCode.toDataURL(`https://${shortUrl}`, {
        width: 300,
        margin: 2,
        color: {
          dark: '#4f46e5', // Indigo color
          light: '#ffffff'
        }
      });
    } catch (qrError) {
      console.warn('Error generating QR code:', qrError);
      // Don't fail the whole process if QR code fails
    }

    // Add to history
    history.value.unshift(newLink);
    if (history.value.length > 5) history.value.pop();
    localStorage.setItem('shortsight_history', JSON.stringify(history.value));

    // Success animation
    confetti({
      particleCount: 100,
      spread: 70,
      origin: { y: 0.6 },
      colors: ['#4f46e5', '#9333ea', '#ec4899'],
    });

  } catch (err) {
    // Handle API errors gracefully
    error.value = err.message;

    // Log for debugging (in production, this would go to error tracking)
    console.error('URL shortening failed:', err);

    // If it's a validation error, try to extract field-specific errors
    if (err.message.includes('slug') || err.message.includes('taken')) {
      slugError.value = err.message;
    }
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  url.value = '';
  customSlug.value = '';
  linkPassword.value = '';
  expiresAt.value = '';
  autoDeleteExpired.value = false;
  result.value = null;
  copied.value = false;
  showOptions.value = false;
  qrCodeUrl.value = '';
  error.value = null;
  slugError.value = null;
  showPasswordField.value = false;
};

const copyToClipboard = () => {
  if (!result.value) return;
  navigator.clipboard.writeText(result.value.shortUrl);
  copied.value = true;
  setTimeout(() => (copied.value = false), 2000);
};

const copyHistoryItem = (text) => {
  navigator.clipboard.writeText(text);
};

const downloadQRCode = () => {
  if (!qrCodeUrl.value) return;

  // Create a temporary link element
  const link = document.createElement('a');
  link.href = qrCodeUrl.value;
  link.download = `qr-${result.value.shortUrl.replace('/', '-')}.png`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
</script>

