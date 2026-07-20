@props(['class' => 'h-9 w-9'])

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 48 48" fill="none" stroke="currentColor"
    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="M24 8v32" />
    <path d="M15 42h18" />
    <path d="M9 14h30" />
    <circle cx="24" cy="10" r="2.5" />
    <path d="M12 14 6 27a6.5 6.5 0 0 0 12 0l-6-13z" />
    <path d="M36 14l-6 13a6.5 6.5 0 0 0 12 0l-6-13z" />
</svg>
