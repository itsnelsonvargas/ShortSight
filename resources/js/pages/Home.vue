
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
                <button @click="showLoginModal = true" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl font-medium hover:bg-slate-800 transition-all transform hover:-translate-y-0.5 hover:shadow-lg">
                  Get Started
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
          <!-- Hero Content ... keep the same -->
          <!-- Main Shortener Card ... keep the same -->
          <!-- Recent Links History ... keep the same -->
        </div>
      </section>
  
      <!-- Features Grid -->
      <section id="features" class="py-20 bg-white">
        <!-- Features content ... keep the same -->
      </section>
  
      <!-- Pricing Section -->
      <section id="pricing" class="py-20 bg-slate-50 border-t border-slate-200">
        <!-- Pricing content ... keep the same -->
      </section>
  
      <!-- Footer -->
      <footer class="bg-white border-t border-slate-200 py-12">
        <!-- Footer content ... keep the same -->
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
  
  const url = ref('');
  const customSlug = ref('');
  const loading = ref(false);
  const result = ref(null);
  const copied = ref(false);
  const showOptions = ref(false);
  const isScrolled = ref(false);
  const showLoginModal = ref(false);
  const mobileMenuOpen = ref(false);
  
  // Mock Auth State
  const isAuthenticated = ref(false);
  const user = ref({ name: 'Alex Doe' });
  
  // History stored in LocalStorage
  const history = ref([]);
  
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
    }, 1000);
  };
  
  const logout = () => {
    isAuthenticated.value = false;
  };
  
  const shortenUrl = () => {
    if (!url.value) return;
    loading.value = true;
  
    setTimeout(() => {
      const code = customSlug.value || Math.random().toString(36).substring(7);
      const newLink = {
        originalUrl: url.value,
        shortUrl: `short.sight/${code}`,
        clicks: 0,
        date: new Date().toLocaleDateString(),
      };
  
      result.value = newLink;
      loading.value = false;
  
      history.value.unshift(newLink);
      if (history.value.length > 5) history.value.pop();
      localStorage.setItem('shortsight_history', JSON.stringify(history.value));
  
      confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 },
        colors: ['#4f46e5', '#9333ea', '#ec4899'],
      });
    }, 1200);
  };
  
  const resetForm = () => {
    url.value = '';
    customSlug.value = '';
    result.value = null;
    copied.value = false;
    showOptions.value = false;
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
  </script>
  
  <style scoped>
  body { font-family: 'Inter', sans-serif; }
  
  .glass-panel {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.5);
  }
  
  .glass-nav {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(12px);
  }
  
  .blob {
    position: absolute;
    filter: blur(80px);
    z-index: 0;
    opacity: 0.6;
    animation: float 10s infinite ease-in-out;
  }
  
  @keyframes float {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(20px, -20px); }
  }
  
  .animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
  }
  
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  
  [v-cloak] { display: none; }
  </style>
  