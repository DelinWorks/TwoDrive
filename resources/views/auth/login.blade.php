<x-app>
    <div class="max-w-md mx-auto flex flex-col sm:h-[calc(100vh-188px)] justify-center">
        <div class="bg-white rounded-3xl shadow-lg p-6 m-3">
            <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 @error('username') border-red-500 @enderror" required>
                    @error('username')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" name="password" id="password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 @error('password') border-red-500 @enderror" required>
                    @error('password')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Login
                </button>
            </form>

            <p class="text-center mt-4">
                Don't have an account? <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800">Register here</a>
            </p>
        </div>
    </div>
</x-app>