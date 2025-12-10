@extends('app')

@section('content')
<div class="min-h-screen bg-slate-50 flex items-center justify-center px-4">
  <div class="max-w-md w-full text-center">
    <!-- Error Icon -->
    <div class="w-24 h-24 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-8">
      <i class="ph ph-file-x text-4xl text-slate-400"></i>
    </div>

    <!-- Error Message -->
    <h1 class="text-4xl font-bold text-slate-900 mb-4">{{ $title ?? 'Page Not Found' }}</h1>
    <p class="text-slate-600 mb-8 leading-relaxed">
      {{ $message ?? 'The page you\'re looking for doesn\'t exist or has been moved.' }}
    </p>

    <!-- Actions -->
    <div class="space-y-4">
      <button onclick="history.back()"
              class="w-full py-3 px-6 bg-slate-900 text-white rounded-xl font-semibold hover:bg-slate-800 transition-colors flex items-center justify-center gap-2">
        <i class="ph ph-arrow-left"></i>
        Go Back
      </button>

      <a href="{{ url('/') }}"
         class="w-full py-3 px-6 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
        <i class="ph ph-house"></i>
        Back to Home
      </a>
    </div>

    <!-- Help Text -->
    <p class="text-sm text-slate-500 mt-8">
      If you believe this is an error, please <a href="mailto:support@shortsight.com" class="text-indigo-600 hover:underline">contact support</a>.
    </p>
  </div>
</div>
@endsection
