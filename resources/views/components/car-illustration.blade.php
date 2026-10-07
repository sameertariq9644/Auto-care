@props(['uid' => uniqid('car')])

<svg viewBox="0 0 400 170" {{ $attributes }}>
    <defs>
        <linearGradient id="{{ $uid }}-body" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#9aa3ad" />
            <stop offset=".55" stop-color="#5d6670" />
            <stop offset="1" stop-color="#2b3138" />
        </linearGradient>
        <linearGradient id="{{ $uid }}-glass" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#1f2937" />
            <stop offset="1" stop-color="#0b0f14" />
        </linearGradient>
        <radialGradient id="{{ $uid }}-rim">
            <stop offset="0" stop-color="#cbd5e1" />
            <stop offset=".65" stop-color="#475569" />
            <stop offset="1" stop-color="#111" />
        </radialGradient>
    </defs>
    <ellipse cx="206" cy="150" rx="170" ry="10" fill="#000" fill-opacity=".18" />
    <path d="M40,120 L44,94 Q48,80 66,76 L122,70 L160,42 Q168,36 184,36 L292,36 Q308,36 318,47 L346,74 L364,80 Q378,85 379,100 L379,120 Q379,127 371,127 L339,127 A33,33 0 0 0 273,127 L139,127 A33,33 0 0 0 73,127 L47,127 Q40,127 40,120 Z" fill="url(#{{ $uid }}-body)" />
    <path d="M134,71 L166,46 Q172,42 181,42 L226,42 L226,71 Z" fill="url(#{{ $uid }}-glass)" />
    <path d="M234,42 L289,42 Q299,42 307,50 L331,73 L234,73 Z" fill="url(#{{ $uid }}-glass)" />
    <path d="M60,96 L372,96" stroke="#e2e8f0" stroke-width="1.2" stroke-opacity=".5" />
    <rect x="360" y="86" width="16" height="6" rx="3" fill="#e0f2fe" />
    <rect x="42" y="92" width="10" height="7" rx="2" fill="#ef4444" />
    <circle cx="106" cy="127" r="28" fill="#0a0a0a" />
    <circle cx="106" cy="127" r="18" fill="url(#{{ $uid }}-rim)" />
    <circle cx="106" cy="127" r="5" fill="#111" />
    <circle cx="306" cy="127" r="28" fill="#0a0a0a" />
    <circle cx="306" cy="127" r="18" fill="url(#{{ $uid }}-rim)" />
    <circle cx="306" cy="127" r="5" fill="#111" />
</svg>
