<div class="flex items-center mb-4">
    <input type="hidden" name="{{ $name }}" value="0"> <!-- Campo oculto para valor false -->
    <input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:bg-white dark:border-gray-400">
    <label for="{{ $name }}" class="ml-2 text-sm font-medium text-gray-800 dark:text-black">{{ $label }}</label>
</div>
