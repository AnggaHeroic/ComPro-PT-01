<svg
    {{ $attributes->merge([
        'xmlns' => 'http://www.w3.org/2000/svg',
        'viewBox' => '0 0 760 320',
        'fill' => 'none',
        'aria-hidden' => 'true',
    ]) }}
>
    {{-- FIVE: selebar angka nol; batang atas, lalu lengkung bundar --}}
    <path
        d="
            M245 45
            H65.8
            V120
            A105 85 0 1 1 65.8 240
        "
        stroke="currentColor"
        stroke-width="15"
        stroke-linecap="square"
        stroke-linejoin="miter"
    />

    {{-- ZERO (sama seperti error 404) --}}
    <circle
        cx="385"
        cy="155"
        r="105"
        stroke="currentColor"
        stroke-width="15"
    />
    <path
        d="
            M311 81
            L459 229
        "
        stroke="currentColor"
        stroke-width="15"
        stroke-linecap="round"
    />

    {{-- ZERO DENGAN TANDA SERU --}}
    <circle
        cx="635"
        cy="155"
        r="105"
        stroke="currentColor"
        stroke-width="15"
    />
    <path
        d="
            M635 98
            V175
        "
        stroke="currentColor"
        stroke-width="15"
        stroke-linecap="square"
    />
    <circle
        cx="635"
        cy="217"
        r="9"
        fill="currentColor"
    />
</svg>
