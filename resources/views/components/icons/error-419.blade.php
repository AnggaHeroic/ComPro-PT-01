<svg
    {{ $attributes->merge([
        'xmlns' => 'http://www.w3.org/2000/svg',
        'viewBox' => '0 0 760 320',
        'fill' => 'none',
        'aria-hidden' => 'true',
    ]) }}
>
    {{-- FOUR --}}
    <path
        d="
            M45 215
            L175 45
            V265
            M45 215
            H205
        "
        stroke="currentColor"
        stroke-width="15"
        stroke-linecap="square"
        stroke-linejoin="miter"
    />

    {{-- ONE --}}
    <path
        d="
            M346.5 100
            L406.5 45
            V265
        "
        stroke="currentColor"
        stroke-width="15"
        stroke-linecap="square"
        stroke-linejoin="miter"
    />

    {{-- NINE: lingkaran lebih kecil, batang dan kait tetap setinggi angka 4 dan 1 --}}
    <circle
        cx="630"
        cy="135"
        r="85"
        stroke="currentColor"
        stroke-width="15"
    />
    <path
        d="
            M715 135
            V195
            A70 70 0 0 1 645 265
            H570
        "
        stroke="currentColor"
        stroke-width="15"
        stroke-linecap="square"
        stroke-linejoin="round"
    />
</svg>
