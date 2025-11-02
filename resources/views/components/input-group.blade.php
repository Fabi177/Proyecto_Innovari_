<div class="mb-4">
    <label for="{{ $name }}" class="block text-sm font-medium text-black dark:text-black">{{ $label }}</label>
    <input type="{{ $type ?? 'text' }}" id="{{ $name }}" name="{{ $name }}" class="block w-full mt-1 text-black bg-white border-gray-300 rounded-md shadow-sm dark:bg-white dark:text-black focus:ring-blue-500 focus:border-blue-500 sm:text-sm" {{ $attributes }}>
</div>
