<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload Invoice') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('invoices.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-6">
                            <label for="invoice_file" class="block text-sm font-medium text-gray-700 mb-2">
                                Invoice File
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="invoice_file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload a file</span>
                                            <input id="invoice_file" name="invoice_file" type="file" class="sr-only" accept=".jpg,.jpeg,.png,.pdf">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        JPG, PNG, or PDF up to 10MB
                                    </p>
                                </div>
                            </div>

                            @error('invoice_file')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- File preview -->
                        <div id="file-preview" class="mb-6 hidden">
                            <p class="text-sm text-gray-600">Selected file: <span id="file-name" class="font-medium"></span></p>
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('invoices.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Parse Invoice') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-6 bg-blue-50 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-blue-800">
                    <h3 class="font-semibold mb-2">How it works</h3>
                    <ol class="list-decimal list-inside space-y-1 text-sm">
                        <li>Upload an invoice image (JPG, PNG) or PDF</li>
                        <li>Claude AI will analyze the document and extract structured data</li>
                        <li>Review the extracted information including line items, totals, and more</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('invoice_file').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                document.getElementById('file-name').textContent = fileName;
                document.getElementById('file-preview').classList.remove('hidden');
            } else {
                document.getElementById('file-preview').classList.add('hidden');
            }
        });
    </script>
</x-app-layout>
