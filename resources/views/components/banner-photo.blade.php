{{-- Photo layer for the blue page banners: a blue-tinted image that fades out behind the title. Place first inside the banner. --}}
@props(['src', 'position' => 'object-center'])

<img src="{{ asset($src) }}" alt="" class="pointer-events-none absolute inset-0 h-full w-full object-cover {{ $position }} opacity-50 mix-blend-luminosity" />
<div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-rx-accent via-rx-accent/85 to-rx-accent/25"></div>
