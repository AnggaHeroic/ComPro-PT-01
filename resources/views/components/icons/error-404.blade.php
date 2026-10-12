<svg
    {{ $attributes->merge([
        'xmlns' => 'http://www.w3.org/2000/svg',
        'viewBox' => '0 0 760 320',
        'fill' => 'none',
        'aria-hidden' => 'true',
    ]) }}
>
    {{-- FIRST 4 --}}
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

    {{-- ZERO --}}
    <circle
        cx="380"
        cy="155"
        r="105"
        stroke="currentColor"
        stroke-width="15"
    />

    {{-- ZERO CROSS LINE --}}
    <path
        d="
            M306 81
            L454 229
        "
        stroke="currentColor"
        stroke-width="15"
        stroke-linecap="round"
    />

    {{-- SECOND 4 --}}
    <path
        d="
            M555 215
            L685 45
            V265
            M555 215
            H715
        "
        stroke="currentColor"
        stroke-width="15"
        stroke-linecap="square"
        stroke-linejoin="miter"
    />
</svg>