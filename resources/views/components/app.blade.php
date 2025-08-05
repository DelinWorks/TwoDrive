<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <script src="{{ mix('js/app.js') }}" defer></script>
    <script src="//unpkg.com/@alpinejs/intersect" defer></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TwoDrive</title>
</head>
<body class="bg-gray-900 max-w-6xl m-auto select-none mb-96">
    <nav class="bg-white m-3 rounded-3xl shadow-lg">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-2">
                    <a href="/" class="text-xl font-bold text-gray-800">TwoDrive</a>
                    <a class="text-gray-500 text-md hidden sm:block">— A OneDrive clone, even better 👌</a>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <span class="text-gray-700">Welcome, {{ Auth::user()->username }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800">Login</a>
                        <a href="{{ route('register') }}" class="text-green-600 hover:text-green-800">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    <div>
        {{ $slot }}
    </div>
</body>
</html>