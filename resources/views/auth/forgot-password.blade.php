@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-50 px-4">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Forgot your password?</h1>
            <p class="text-sm text-gray-500 mt-2">Enter your email and we'll send you a reset link.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">

            @if(session('status'))
            <div class="mb-5 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none @error('email') border-red-400 @enderror">
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    Send Reset Link
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">
                        ← Back to login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection