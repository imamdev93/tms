{{-- <x-filament::page>
    <x-filament::card>
        <div class="flex flex-col items-center">
            <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                <!-- Card Header -->
                <div class="bg-primary-500 py-4 px-6">
                    <h2 class="text-xl font-bold text-white text-center">MEMBERSHIP CARD</h2>
                </div>
                
                <!-- Card Body -->
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-semibold">{{ $card->name }}</h3>
                            <p class="text-sm text-gray-600">Member ID: {{ $card->registration_number }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                            {{ strtoupper($card->status) }}
                        </span>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg mb-6 text-center">
                        <p class="text-sm text-gray-500 mb-1">Card Number</p>
                        <p class="font-mono text-xl tracking-widest">{{ $card->registration_number }}</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Issued Date</p>
                            <p class="font-medium">{{ $card->start_date ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Expiry Date</p>
                            <p class="font-medium">{{ $card->end_date ?? '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="flex justify-center">

                    </div>
                </div>
            </div>
            <br>
            <div class="flex gap-4 mt-10">
                <x-filament::button wire:click="downloadPdf" icon="heroicon-o-document-arrow-down">
                    Download PDF
                </x-filament::button>
            </div>
        </div>
    </x-filament::card>
</x-filament::page> --}}

    <x-filament::page>
        <x-filament::card>
            <div class="flex flex-col items-center">
                <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <!-- Card Header -->
                    <div class="bg-primary-500 py-4 px-6">
                        <h2 class="text-xl font-bold text-white text-center">MEMBERSHIP CARD</h2>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="p-6">
                        <div class="flex items-start gap-4 mb-6">
                            <!-- Profile Photo -->
                            <div class="flex-shrink-0">
                                @if($card->photo_file_path)
                                    <img src="/storage/{{ $card->photo_file_path }}" alt="Profile Photo" 
                                        class="w-20 h-20 rounded-full object-cover border-4 border-primary-100">
                                @else
                                    <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center border-4 border-primary-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Member Info -->
                            <div class="flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-semibold">{{ $card->name }}</h3>
                                        <p class="text-sm text-gray-600 font-semibold">{{ $card->registration_number }}</p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-danger">
                                        {{ strtoupper($card->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <br>
                        <!-- Card Number Highlight -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6 text-center">
                            <p class="text-sm text-gray-500 mb-1">Card Number</p>
                            <p class="font-mono text-xl tracking-widest font-bold text-primary-600">{{ $card->registration_number }}</p>
                        </div>
                        <br>
                        <!-- Dates Section -->
                        <div class="grid grid-cols-2 gap-10 mb-10">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm text-gray-500">Expiry Date</p>
                                <p class="font-medium">{{ $card->end_date ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <!-- Download Button -->
                <div class="flex gap-4 mt-20">
                    <x-filament::button wire:click="downloadPdf" icon="heroicon-o-document-arrow-down" size="md">
                        Download PDF
                    </x-filament::button>
                </div>
            </div>
        </x-filament::card>
    </x-filament::page>