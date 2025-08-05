<x-app>
    <div class="max-w-md mx-auto flex flex-col sm:h-[calc(100vh-188px)] justify-center">
        <div class="bg-white rounded-3xl shadow-lg p-6 m-3">
            <h2 class="text-2xl font-bold mb-6 text-center">Register</h2>
            
            <form method="POST" action="{{ route('register') }}">
                @csrf
                
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 @error('username') border-red-500 @enderror" required>
                    @error('username')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" name="password" id="password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 @error('password') border-red-500 @enderror" required>
                    @error('password')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500" required>
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Register
                </button>
            </form>

            <p class="text-center mt-4">
                Already have an account? <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800">Login here</a>
            </p>
        </div>
    </div>
</x-app>