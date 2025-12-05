<template>
  <div class="min-h-screen bg-slate-50">
    <!-- Dashboard Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <router-link to="/" class="flex items-center gap-2 cursor-pointer">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
              <i class="ph ph-link text-xl font-bold"></i>
            </div>
            <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-800 to-slate-600">
              Short<span class="text-indigo-600">Sight</span>
            </span>
          </router-link>

          <!-- User Menu -->
          <div class="flex items-center gap-4">
            <button @click="showCreateModal = true" class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-all flex items-center gap-2">
              <i class="ph ph-plus-circle text-lg"></i>
              <span class="hidden sm:inline">New Link</span>
            </button>
            <div class="flex items-center gap-3">
              <div class="hidden sm:flex flex-col text-right">
                <span class="text-sm font-bold text-slate-800">{{ user.name }}</span>
                <span class="text-xs text-slate-500">{{ user.plan }}</span>
              </div>
              <button @click="logout" class="p-2 text-slate-500 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                <i class="ph ph-sign-out text-xl"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">Dashboard</h1>
        <p class="text-slate-600">Manage your links and track performance</p>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Links -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
              <i class="ph ph-link text-2xl text-indigo-600"></i>
            </div>
            <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-full">+12%</span>
          </div>
          <h3 class="text-2xl font-bold text-slate-900 mb-1">{{ stats.totalLinks }}</h3>
          <p class="text-sm text-slate-500">Total Links</p>
        </div>

        <!-- Total Clicks -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
              <i class="ph ph-cursor-click text-2xl text-purple-600"></i>
            </div>
            <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-full">+24%</span>
          </div>
          <h3 class="text-2xl font-bold text-slate-900 mb-1">{{ stats.totalClicks.toLocaleString() }}</h3>
          <p class="text-sm text-slate-500">Total Clicks</p>
        </div>

        <!-- Click Rate -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center">
              <i class="ph ph-chart-line-up text-2xl text-pink-600"></i>
            </div>
            <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-full">+8%</span>
          </div>
          <h3 class="text-2xl font-bold text-slate-900 mb-1">{{ stats.avgClickRate }}%</h3>
          <p class="text-sm text-slate-500">Avg. Click Rate</p>
        </div>

        <!-- Top Performer -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
              <i class="ph ph-trophy text-2xl text-yellow-600"></i>
            </div>
          </div>
          <h3 class="text-2xl font-bold text-slate-900 mb-1">{{ stats.topPerformer.clicks }}</h3>
          <p class="text-sm text-slate-500 truncate">{{ stats.topPerformer.name }}</p>
        </div>
      </div>

      <!-- Analytics Chart -->
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 mb-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-slate-900">Click Analytics</h2>
          <div class="flex gap-2">
            <button
              v-for="period in ['7d', '30d', '90d']"
              :key="period"
              @click="selectedPeriod = period"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                selectedPeriod === period
                  ? 'bg-indigo-600 text-white'
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
              ]"
            >
              {{ period }}
            </button>
          </div>
        </div>
        <div class="h-64">
          <Line :data="chartData" :options="chartOptions" />
        </div>
      </div>

      <!-- Links Management Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
              <h2 class="text-xl font-bold text-slate-900">Your Links</h2>
              <span v-if="selectedLinks.length > 0" class="text-sm text-slate-500">
                {{ selectedLinks.length }} selected
              </span>
            </div>

            <!-- Actions for selected links -->
            <div v-if="selectedLinks.length > 0" class="flex gap-2">
              <button
                @click="viewSelectedAnalytics"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors flex items-center gap-2"
              >
                <i class="ph ph-chart-bar"></i>
                View Analytics
              </button>
              <button
                @click="clearSelection"
                class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg font-medium hover:bg-slate-200 transition-colors"
              >
                Clear Selection
              </button>
            </div>

            <!-- Search and Filter -->
            <div class="flex gap-2 w-full sm:w-auto">
              <div class="relative flex-1 sm:flex-none">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search links..."
                  class="w-full sm:w-64 pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10"
                >
              </div>
              <button class="p-2 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 transition-colors">
                <i class="ph ph-funnel text-slate-600"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider w-12">
                  <input
                    type="checkbox"
                    :checked="selectedLinks.length === filteredLinks.length && filteredLinks.length > 0"
                    :indeterminate="selectedLinks.length > 0 && selectedLinks.length < filteredLinks.length"
                    @change="toggleSelectAll"
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                  >
                </th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                  <button @click="sortBy('shortUrl')" class="flex items-center gap-1 hover:text-slate-700">
                    Short Link
                    <i class="ph ph-caret-down text-sm"></i>
                  </button>
                </th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden lg:table-cell">
                  Original URL
                </th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                  <button @click="sortBy('clicks')" class="flex items-center gap-1 hover:text-slate-700">
                    Clicks
                    <i class="ph ph-caret-down text-sm"></i>
                  </button>
                </th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">
                  <button @click="sortBy('date')" class="flex items-center gap-1 hover:text-slate-700">
                    Created
                    <i class="ph ph-caret-down text-sm"></i>
                  </button>
                </th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="link in filteredLinks" :key="link.id" class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4">
                  <input
                    type="checkbox"
                    :checked="isSelected(link.id)"
                    @change="toggleLinkSelection(link)"
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                  >
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <a :href="link.shortUrl" target="_blank" class="text-indigo-600 font-semibold hover:underline">
                      {{ link.shortUrl }}
                    </a>
                    <button @click="copyLink(link.shortUrl)" class="p-1 text-slate-400 hover:text-indigo-600 transition-colors">
                      <i class="ph ph-copy text-sm"></i>
                    </button>
                  </div>
                </td>
                <td class="px-6 py-4 hidden lg:table-cell">
                  <p class="text-sm text-slate-600 truncate max-w-xs">{{ link.originalUrl }}</p>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">{{ link.clicks.toLocaleString() }}</span>
                    <span v-if="link.clickTrend > 0" class="text-xs text-green-600">↑ {{ link.clickTrend }}%</span>
                    <span v-else-if="link.clickTrend < 0" class="text-xs text-red-600">↓ {{ Math.abs(link.clickTrend) }}%</span>
                  </div>
                </td>
                <td class="px-6 py-4 hidden md:table-cell">
                  <span class="text-sm text-slate-600">{{ link.date }}</span>
                </td>
                <td class="px-6 py-4">
                  <span :class="[
                    'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold',
                    link.status === 'active' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600'
                  ]">
                    <span class="w-1.5 h-1.5 rounded-full" :class="link.status === 'active' ? 'bg-green-500' : 'bg-slate-400'"></span>
                    {{ link.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="viewAnalytics(link)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="View Analytics">
                      <i class="ph ph-chart-bar"></i>
                    </button>
                    <button @click="editLink(link)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                      <i class="ph ph-pencil-simple"></i>
                    </button>
                    <button @click="downloadQR(link)" class="p-2 text-slate-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="Download QR">
                      <i class="ph ph-qr-code"></i>
                    </button>
                    <button @click="deleteLink(link)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                      <i class="ph ph-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Selected Links Analytics Modal -->
        <div v-if="showAnalyticsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showAnalyticsModal = false"></div>
          <div class="bg-white rounded-3xl max-w-6xl w-full max-h-[90vh] overflow-hidden relative z-10 shadow-2xl">
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
              <h3 class="text-2xl font-bold text-slate-900">
                Link Analytics
                <span class="text-lg font-normal text-slate-500">({{ selectedLinks.length }} link{{ selectedLinks.length > 1 ? 's' : '' }})</span>
              </h3>
              <button @click="showAnalyticsModal = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <i class="ph ph-x text-xl"></i>
              </button>
            </div>

            <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
              <!-- Selected Links Summary -->
              <div class="grid grid-cols-1 md:grid-cols-{{ selectedLinks.length > 3 ? '2' : selectedLinks.length }} gap-4 mb-8">
                <div v-for="link in selectedAnalyticsLinks" :key="link.id" class="bg-slate-50 rounded-xl p-4">
                  <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                      <i class="ph ph-link text-indigo-600"></i>
                    </div>
                    <div>
                      <h4 class="font-semibold text-slate-900">{{ link.shortUrl }}</h4>
                      <p class="text-sm text-slate-500 truncate max-w-xs">{{ link.originalUrl }}</p>
                    </div>
                  </div>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <p class="text-2xl font-bold text-slate-900">{{ link.clicks.toLocaleString() }}</p>
                      <p class="text-sm text-slate-500">Total Clicks</p>
                    </div>
                    <div>
                      <p class="text-2xl font-bold text-green-600">{{ link.clickTrend }}%</p>
                      <p class="text-sm text-slate-500">Trend</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Time Period Selector -->
              <div class="flex items-center justify-between mb-6">
                <h4 class="text-lg font-semibold text-slate-900">Click Trends</h4>
                <div class="flex gap-2">
                  <button
                    v-for="period in ['7d', '30d', '90d']"
                    :key="period"
                    @click="analyticsPeriod = period"
                    :class="[
                      'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                      analyticsPeriod === period
                        ? 'bg-indigo-600 text-white'
                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                    ]"
                  >
                    {{ period }}
                  </button>
                </div>
              </div>

              <!-- Analytics Chart -->
              <div class="bg-slate-50 rounded-xl p-6 mb-6">
                <div class="h-80">
                  <Line :data="analyticsChartData" :options="analyticsChartOptions" />
                </div>
              </div>

              <!-- Geographic Data -->
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Countries -->
                <div class="bg-slate-50 rounded-xl p-6">
                  <h4 class="text-lg font-semibold text-slate-900 mb-4">Top Countries</h4>
                  <div class="space-y-3">
                    <div v-for="country in topCountries" :key="country.name" class="flex items-center justify-between">
                      <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-slate-900">{{ country.name }}</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <div class="w-20 bg-slate-200 rounded-full h-2">
                          <div
                            class="bg-indigo-600 h-2 rounded-full"
                            :style="{ width: (country.clicks / topCountries[0].clicks * 100) + '%' }"
                          ></div>
                        </div>
                        <span class="text-sm font-medium text-slate-700">{{ country.clicks }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Device Breakdown -->
                <div class="bg-slate-50 rounded-xl p-6">
                  <h4 class="text-lg font-semibold text-slate-900 mb-4">Device Types</h4>
                  <div class="space-y-4">
                    <div v-for="device in deviceBreakdown" :key="device.type" class="flex items-center justify-between">
                      <div class="flex items-center gap-3">
                        <i :class="device.icon" class="text-slate-600"></i>
                        <span class="text-sm font-medium text-slate-900">{{ device.type }}</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <div class="w-20 bg-slate-200 rounded-full h-2">
                          <div
                            class="bg-indigo-600 h-2 rounded-full"
                            :style="{ width: device.percentage + '%' }"
                          ></div>
                        </div>
                        <span class="text-sm font-medium text-slate-700">{{ device.percentage }}%</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
          <p class="text-sm text-slate-600">
            Showing <span class="font-semibold">1</span> to <span class="font-semibold">{{ filteredLinks.length }}</span> of <span class="font-semibold">{{ links.length }}</span> links
          </p>
          <div class="flex gap-2">
            <button class="px-3 py-2 border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
              Previous
            </button>
            <button class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
              1
            </button>
            <button class="px-3 py-2 border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50" disabled>
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Link Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showCreateModal = false"></div>
      <div class="bg-white rounded-3xl p-8 max-w-lg w-full relative z-10 shadow-2xl animate-fade-in-up">
        <button @click="showCreateModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
          <i class="ph ph-x text-2xl"></i>
        </button>
        <h3 class="text-2xl font-bold text-slate-900 mb-6">Create New Link</h3>

        <form @submit.prevent="createLink" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Destination URL</label>
            <input
              v-model="newLink.url"
              type="url"
              placeholder="https://example.com/your-long-url"
              required
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10"
            >
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Custom Alias (Optional)</label>
            <div class="flex">
              <span class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-slate-200 bg-slate-50 text-slate-500 text-sm">short.sight/</span>
              <input
                v-model="newLink.slug"
                type="text"
                placeholder="my-link"
                class="flex-1 px-4 py-3 border border-slate-200 rounded-r-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10"
              >
            </div>
          </div>

          <button
            type="submit"
            class="w-full py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-colors"
          >
            Create Short Link
          </button>
        </form>
      </div>
    </div>

    <!-- Edit Link Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showEditModal = false"></div>
      <div class="bg-white rounded-3xl p-8 max-w-lg w-full relative z-10 shadow-2xl animate-fade-in-up">
        <button @click="showEditModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
          <i class="ph ph-x text-2xl"></i>
        </button>
        <h3 class="text-2xl font-bold text-slate-900 mb-6">Edit Link</h3>

        <form @submit.prevent="updateLink" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Destination URL</label>
            <input
              v-model="editingLink.originalUrl"
              type="url"
              required
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10"
            >
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Short URL</label>
            <input
              v-model="editingLink.shortUrl"
              type="text"
              disabled
              class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 cursor-not-allowed"
            >
          </div>

          <div class="flex gap-3">
            <button
              type="button"
              @click="showEditModal = false"
              class="flex-1 py-3 bg-slate-100 text-slate-700 rounded-xl font-bold hover:bg-slate-200 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="flex-1 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-colors"
            >
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js';

// Register ChartJS components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

const router = useRouter();

// User data
const user = ref({
  name: 'Alex Doe',
  plan: 'Pro Plan'
});

// Stats
const stats = ref({
  totalLinks: 24,
  totalClicks: 12847,
  avgClickRate: 3.2,
  topPerformer: {
    name: 'Product Launch',
    clicks: 4521
  }
});

// Chart data
const selectedPeriod = ref('7d');

// Mock data for different time periods
const chartDatasets = {
  '7d': {
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    data: [320, 445, 380, 510, 475, 620, 590]
  },
  '30d': {
    labels: Array.from({length: 30}, (_, i) => {
      const date = new Date();
      date.setDate(date.getDate() - (29 - i));
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }),
    data: Array.from({length: 30}, (_, i) => {
      // Create a pattern with higher clicks on weekdays
      const baseClicks = 300 + Math.sin(i / 7 * 2 * Math.PI) * 100;
      const dayOfWeek = i % 7;
      const weekdayBonus = (dayOfWeek >= 0 && dayOfWeek <= 4) ? 150 : -50;
      return Math.floor(baseClicks + weekdayBonus + Math.random() * 100);
    })
  },
  '90d': {
    labels: Array.from({length: 13}, (_, i) => {
      const date = new Date();
      date.setDate(date.getDate() - (89 - i * 7));
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }),
    data: Array.from({length: 13}, (_, i) => {
      // Create weekly trends with realistic patterns
      const baseClicks = 2000 + Math.sin(i / 13 * 2 * Math.PI) * 500;
      const weeklyPattern = (i % 4 === 0) ? 800 : 0; // Higher clicks every 4 weeks
      return Math.floor(baseClicks + weeklyPattern + Math.random() * 300);
    })
  }
};

const chartData = computed(() => ({
  labels: chartDatasets[selectedPeriod.value].labels,
  datasets: [
    {
      label: 'Clicks',
      data: chartDatasets[selectedPeriod.value].data,
      borderColor: '#4f46e5',
      backgroundColor: 'rgba(79, 70, 229, 0.1)',
      tension: 0.4,
      fill: true,
      pointRadius: selectedPeriod.value === '7d' ? 4 : 2,
      pointHoverRadius: selectedPeriod.value === '7d' ? 6 : 4,
      pointBackgroundColor: '#4f46e5',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
    }
  ]
}));

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: '#1e293b',
      padding: 12,
      borderRadius: 8,
      titleFont: {
        size: 14,
        weight: 'bold'
      },
      bodyFont: {
        size: 13
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: '#f1f5f9'
      },
      ticks: {
        color: '#64748b'
      }
    },
    x: {
      grid: {
        display: false
      },
      ticks: {
        color: '#64748b'
      }
    }
  }
});

// Mock links data
const links = ref([
  {
    id: 1,
    shortUrl: 'short.sight/product',
    originalUrl: 'https://example.com/my-awesome-product-launch-2024',
    clicks: 4521,
    clickTrend: 12,
    date: '2024-11-28',
    status: 'active'
  },
  {
    id: 2,
    shortUrl: 'short.sight/blog',
    originalUrl: 'https://blog.example.com/how-to-grow-your-business',
    clicks: 2847,
    clickTrend: 8,
    date: '2024-11-25',
    status: 'active'
  },
  {
    id: 3,
    shortUrl: 'short.sight/promo',
    originalUrl: 'https://example.com/black-friday-deals',
    clicks: 1920,
    clickTrend: -5,
    date: '2024-11-20',
    status: 'active'
  },
  {
    id: 4,
    shortUrl: 'short.sight/demo',
    originalUrl: 'https://example.com/product-demo-video',
    clicks: 1543,
    clickTrend: 15,
    date: '2024-11-18',
    status: 'active'
  },
  {
    id: 5,
    shortUrl: 'short.sight/social',
    originalUrl: 'https://instagram.com/mycompany',
    clicks: 1016,
    clickTrend: 0,
    date: '2024-11-15',
    status: 'active'
  }
]);

// Search and filter
const searchQuery = ref('');
const sortField = ref('clicks');
const sortDirection = ref('desc');

// Link selection
const selectedLinks = ref([]);
const showAnalyticsModal = ref(false);
const analyticsPeriod = ref('7d');

const filteredLinks = computed(() => {
  let result = links.value;

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(link =>
      link.shortUrl.toLowerCase().includes(query) ||
      link.originalUrl.toLowerCase().includes(query)
    );
  }

  // Sort
  result = [...result].sort((a, b) => {
    const aVal = a[sortField.value];
    const bVal = b[sortField.value];

    if (sortDirection.value === 'asc') {
      return aVal > bVal ? 1 : -1;
    } else {
      return aVal < bVal ? 1 : -1;
    }
  });

  return result;
});

// Selected links analytics
const selectedAnalyticsLinks = computed(() => {
  return links.value.filter(link => selectedLinks.value.includes(link.id));
});

const analyticsChartData = computed(() => {
  const datasets = selectedAnalyticsLinks.value.map((link, index) => {
    const colors = ['#4f46e5', '#7c3aed', '#db2777', '#ea580c', '#16a34a'];
    const color = colors[index % colors.length];

    return {
      label: link.shortUrl.split('/').pop(),
      data: generateAnalyticsData(link, analyticsPeriod.value),
      borderColor: color,
      backgroundColor: color + '20',
      tension: 0.4,
      fill: false,
      pointRadius: 3,
      pointHoverRadius: 5,
      pointBackgroundColor: color,
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
    };
  });

  return {
    labels: getAnalyticsLabels(analyticsPeriod.value),
    datasets
  };
});

// Mock analytics data generation
function generateAnalyticsData(link, period) {
  const baseClicks = Math.floor(link.clicks / (period === '7d' ? 7 : period === '30d' ? 30 : 90));
  const variation = 0.3; // 30% variation

  if (period === '7d') {
    return Array.from({length: 7}, () => Math.floor(baseClicks * (1 + (Math.random() - 0.5) * variation)));
  } else if (period === '30d') {
    return Array.from({length: 30}, (_, i) => {
      const dayMultiplier = (i % 7 < 5) ? 1.5 : 0.8; // Higher on weekdays
      return Math.floor(baseClicks * dayMultiplier * (1 + (Math.random() - 0.5) * variation));
    });
  } else {
    return Array.from({length: 13}, () => Math.floor(baseClicks * 7 * (1 + (Math.random() - 0.5) * variation)));
  }
}

function getAnalyticsLabels(period) {
  if (period === '7d') {
    return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  } else if (period === '30d') {
    return Array.from({length: 30}, (_, i) => `Day ${i + 1}`);
  } else {
    return Array.from({length: 13}, (_, i) => `Week ${i + 1}`);
  }
}

// Mock geographic and device data
const topCountries = [
  { name: 'United States', clicks: 1250 },
  { name: 'United Kingdom', clicks: 890 },
  { name: 'Canada', clicks: 650 },
  { name: 'Germany', clicks: 420 },
  { name: 'Australia', clicks: 380 }
];

const deviceBreakdown = [
  { type: 'Desktop', percentage: 55, icon: 'ph ph-monitor' },
  { type: 'Mobile', percentage: 35, icon: 'ph ph-device-mobile' },
  { type: 'Tablet', percentage: 10, icon: 'ph ph-tablet' }
];

const analyticsChartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: {
        usePointStyle: true,
        padding: 20
      }
    },
    tooltip: {
      backgroundColor: '#1e293b',
      padding: 12,
      borderRadius: 8,
      titleFont: {
        size: 14,
        weight: 'bold'
      },
      bodyFont: {
        size: 13
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: '#f1f5f9'
      },
      ticks: {
        color: '#64748b'
      }
    },
    x: {
      grid: {
        display: false
      },
      ticks: {
        color: '#64748b'
      }
    }
  }
});

// Modals
const showCreateModal = ref(false);
const showEditModal = ref(false);
const newLink = ref({
  url: '',
  slug: ''
});
const editingLink = ref(null);

// Functions
const sortBy = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'desc';
  }
};

const copyLink = (url) => {
  navigator.clipboard.writeText(url);
  // Could add a toast notification here
};

const createLink = () => {
  // Mock creation - will be replaced with API call
  const code = newLink.value.slug || Math.random().toString(36).substring(7);
  const shortUrl = `short.sight/${code}`;

  links.value.unshift({
    id: links.value.length + 1,
    shortUrl: shortUrl,
    originalUrl: newLink.value.url,
    clicks: 0,
    clickTrend: 0,
    date: new Date().toISOString().split('T')[0],
    status: 'active'
  });

  stats.value.totalLinks++;

  newLink.value = { url: '', slug: '' };
  showCreateModal.value = false;
};

const editLink = (link) => {
  editingLink.value = { ...link };
  showEditModal.value = true;
};

const updateLink = () => {
  const index = links.value.findIndex(l => l.id === editingLink.value.id);
  if (index !== -1) {
    links.value[index] = { ...editingLink.value };
  }
  showEditModal.value = false;
  editingLink.value = null;
};

const deleteLink = (link) => {
  if (confirm(`Are you sure you want to delete ${link.shortUrl}?`)) {
    const index = links.value.findIndex(l => l.id === link.id);
    if (index !== -1) {
      links.value.splice(index, 1);
      stats.value.totalLinks--;
    }
  }
};

const viewAnalytics = (link) => {
  // Navigate to detailed analytics page (to be created)
  alert(`Analytics for ${link.shortUrl}\n\nThis would show:\n- Click timeline\n- Geographic distribution\n- Device breakdown\n- Referrer sources`);
};

const downloadQR = async (link) => {
  // Use QRCode library to generate and download
  const QRCode = (await import('qrcode')).default;
  const qrDataUrl = await QRCode.toDataURL(`https://${link.shortUrl}`, {
    width: 300,
    margin: 2,
    color: {
      dark: '#4f46e5',
      light: '#ffffff'
    }
  });

  const a = document.createElement('a');
  a.href = qrDataUrl;
  a.download = `qr-${link.shortUrl.replace('/', '-')}.png`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
};

// Link selection functions
const isSelected = (linkId) => {
  return selectedLinks.value.includes(linkId);
};

const toggleLinkSelection = (link) => {
  const index = selectedLinks.value.indexOf(link.id);
  if (index > -1) {
    selectedLinks.value.splice(index, 1);
  } else {
    selectedLinks.value.push(link.id);
  }
};

const toggleSelectAll = () => {
  if (selectedLinks.value.length === filteredLinks.value.length) {
    selectedLinks.value = [];
  } else {
    selectedLinks.value = filteredLinks.value.map(link => link.id);
  }
};

const clearSelection = () => {
  selectedLinks.value = [];
};

const viewSelectedAnalytics = () => {
  if (selectedLinks.value.length > 0) {
    showAnalyticsModal.value = true;
  }
};

const logout = () => {
  // Clear auth and redirect to home
  router.push('/');
};
</script>

<style scoped>
@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fade-in-up 0.3s ease-out;
}
</style>
