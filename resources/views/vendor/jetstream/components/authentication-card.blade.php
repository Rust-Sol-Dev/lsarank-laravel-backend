<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <div class="text-center">
        {{ $headings }}
    </div>
    <div class="text-center">
        {{ $header }}
    </div>

    <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        {{ $slot }}
    </div>
    <div class="text-center">
        {{ $footer }}
    </div>
</div>
