@extends('app')

@section('content')
<div class="min-h-screen bg-slate-50 flex items-center justify-center px-4">
  <div class="max-w-md w-full text-center">
    <!-- Error Icon -->
    <div class="w-24 h-24 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-8">
      <i class="ph ph-link-break text-4xl text-amber-500"></i>
    </div>

    <!-- Error Message -->
    <h1 class="text-4xl font-bold text-slate-900 mb-4">{{ $title ?? 'Link Unavailable' }}</h1>
    <p class="text-slate-600 mb-8 leading-relaxed">
      {{ $message ?? 'We couldn\'t redirect you to this link. It may have been removed, expired, or is temporarily unavailable.' }}
    </p>

    <!-- Service Status Indicator -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
        <span class="text-sm font-medium text-blue-900">Service Status: Operational</span>
      </div>
      <p class="text-sm text-blue-700">Our systems are running normally. This appears to be a link-specific issue.</p>
    </div>

    @if(isset($slug) && $slug)
    <div class="bg-slate-100 rounded-lg p-4 mb-6 text-left">
      <p class="text-sm text-slate-600 mb-2">
        <span class="font-medium">Requested Link:</span>
      </p>
      <code class="bg-slate-200 px-3 py-2 rounded text-sm break-all">{{ $slug }}</code>
    </div>
    @endif

    <!-- Possible Reasons -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
      <h3 class="text-sm font-semibold text-blue-900 mb-2">Possible reasons:</h3>
      <ul class="text-sm text-blue-800 space-y-1">
        <li>• The link may have been deleted by its owner</li>
        <li>• The link may have expired</li>
        <li>• There might be a temporary technical issue</li>
        <li>• The link URL may be incorrect</li>
      </ul>
    </div>

    <!-- Actions -->
    <div class="space-y-4">
      <button onclick="history.back()"
              class="w-full py-3 px-6 bg-slate-900 text-white rounded-xl font-semibold hover:bg-slate-800 transition-colors flex items-center justify-center gap-2">
        <i class="ph ph-arrow-left"></i>
        Go Back
      </button>

      <a href="{{ url('/') }}"
         class="w-full py-3 px-6 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
        <i class="ph ph-plus-circle"></i>
        Create New Link
      </a>
    </div>

    <!-- Help Text -->
    <p class="text-sm text-slate-500 mt-8">
      If you believe this link should work, please <a href="mailto:support@shortsight.com" class="text-indigo-600 hover:underline">contact support</a>.
    </p>
  </div>
</div>
@endsection
