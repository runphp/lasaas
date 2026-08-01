<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <title>{{ $title }}</title>
</head>
<body class="min-h-screen bg-zinc-800 text-white antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-6 text-center">
        <div class="w-full max-w-md rounded-2xl bg-zinc-900 p-8 shadow-xl ring-1 ring-zinc-700">
            <flux:icon.shield-exclamation class="mx-auto size-10 text-amber-500" />

            <h1 class="mt-6 text-2xl font-semibold text-white">{{ $title }}</h1>

            <p class="mt-4 text-sm leading-relaxed text-zinc-300">{{ $message }}</p>

            @if (($retry ?? false))
                <a href="{{ url()->current() }}"
                   class="mt-8 inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-500">
                    {{ __('tenant.unavailable.retry') }}
                </a>
            @endif
        </div>
    </div>
</body>
</html>
