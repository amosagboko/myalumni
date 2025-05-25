<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload Alumni') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Upload Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Upload Alumni Records</h3>
                    
                    <form method="POST" action="{{ route('upload.alumni.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="file" :value="__('Upload File (CSV/Excel)')" />
                            <input type="file" name="file" id="file" class="mt-1 block w-full" accept=".csv,.xlsx,.xls" required>
                            <p class="mt-1 text-sm text-gray-500">Maximum file size: 10MB</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Upload') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search Credentials Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Retrieve Alumni Credentials</h3>
                    
                    <form method="GET" action="{{ route('upload.alumni.credentials') }}" class="space-y-4">
                        <div>
                            <x-input-label for="matriculation_id" :value="__('Matriculation Number')" />
                            <x-text-input id="matriculation_id" name="matriculation_id" type="text" class="mt-1 block w-full" required />
                            <p class="mt-1 text-sm text-gray-500">Enter the matriculation number to retrieve temporary login credentials</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Search') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 