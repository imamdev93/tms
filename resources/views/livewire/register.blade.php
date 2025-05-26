<div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Daftar Akun Baru</h2>
            
            <form wire:submit.prevent="register" class="space-y-6">
                <div class="space-y-4">
                    {{ $this->form }}
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    Daftar
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Sudah punya akun?
                    <a href="/tms/login" class="text-blue-600 hover:text-blue-500 font-medium">
                        Masuk disini
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>