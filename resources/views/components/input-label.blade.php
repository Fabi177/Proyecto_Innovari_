@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-black dark:text-DarkBlue']) }}>
    {{ $value ?? $slot }}
</label>
