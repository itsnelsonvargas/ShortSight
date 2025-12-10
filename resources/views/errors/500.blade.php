@extends('app')

@section('content')
<div class="min-h-screen bg-slate-50 flex items-center justify-center px-4">
  <div class="max-w-md w-full text-center">
    <!-- Error Icon -->
    <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-8">
      <i class="ph ph-warning-circle text-4xl text-red-500"></i>
    </div>

    <!-- Error Message -->
    <h1 class="text-4xl font-bold text-slate-900 mb-4">{{ $title ?? 'Something went wrong' }}</h1>
    <p class="text-slate-600 mb-8 leading-relaxed">
      {{ $message ?? 'We encountered an unexpected error. Our team has been notified and is working to fix it.' }}
    </p>

    @if(isset($error_id))
    <div class="bg-slate-100 rounded-lg p-4 mb-6 text-left">
      <p class="text-sm text-slate-600 mb-2">
        <span class="font-medium">Error ID:</span> <code class="bg-slate-200 px-2 py-1 rounded text-xs">{{ $error_id }}</code>
      </p>
      <p class="text-xs text-slate-500">
        Please include this error ID when contacting support.
      </p>
    </div>
    @endif

    <!-- Actions -->
    <div class="space-y-4">
      <button onclick="window.location.reload()"
              class="w-full py-3 px-6 bg-slate-900 text-white rounded-xl font-semibold hover:bg-slate-800 transition-colors flex items-center justify-center gap-2">
        <i class="ph ph-arrow-clockwise"></i>
        Try Again
      </button>

      <a href="{{ url('/') }}"
         class="w-full py-3 px-6 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
        <i class="ph ph-house"></i>
        Back to Home
      </a>
    </div>

    <!-- Help Text -->
    <p class="text-sm text-slate-500 mt-8">
      If this problem persists, please <a href="mailto:support@shortsight.com" class="text-indigo-600 hover:underline">contact support</a> with the error ID above.
    </p>
  </div>
</div>
@endsection
