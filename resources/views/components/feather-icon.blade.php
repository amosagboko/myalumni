@props([
    'name',
    'size' => 'sm',
    'class' => '',
    'stroke' => 'currentColor',
    'strokeWidth' => '2'
])

@php
    $sizes = [
        'xs' => 'w-4 h-4',
        'sm' => 'w-5 h-5',
        'md' => 'w-6 h-6',
        'lg' => 'w-7 h-7',
        'xl' => 'w-8 h-8'
    ];
    
    $sizeClass = $sizes[$size] ?? $sizes['sm'];
@endphp

<svg 
    {{ $attributes->merge(['class' => "feather feather-{$name} {$sizeClass} {$class}"]) }}
    xmlns="http://www.w3.org/2000/svg" 
    width="24" 
    height="24" 
    viewBox="0 0 24 24" 
    fill="none" 
    stroke="{{ $stroke }}" 
    stroke-width="{{ $strokeWidth }}" 
    stroke-linecap="round" 
    stroke-linejoin="round"
>
    @switch($name)
        @case('heart')
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            @break
        @case('message-circle')
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
            @break
        @case('share-2')
            <circle cx="18" cy="5" r="3"></circle>
            <circle cx="6" cy="12" r="3"></circle>
            <circle cx="18" cy="19" r="3"></circle>
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
            @break
        @case('send')
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            @break
        @case('more-vertical')
            <circle cx="12" cy="12" r="1"></circle>
            <circle cx="12" cy="5" r="1"></circle>
            <circle cx="12" cy="19" r="1"></circle>
            @break
        @default
            <!-- Add more icon cases as needed -->
    @endswitch
</svg> 