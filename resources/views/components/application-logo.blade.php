<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <!-- Money Stack of USD Bills -->
    <defs>
        <linearGradient id="billTop" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#86efac;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#22c55e;stop-opacity:1" />
        </linearGradient>
        <linearGradient id="billMid" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#4ade80;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#16a34a;stop-opacity:1" />
        </linearGradient>
        <linearGradient id="billBot" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#22c55e;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#15803d;stop-opacity:1" />
        </linearGradient>
    </defs>

    <!-- Bottom bill -->
    <rect x="25" y="105" width="150" height="80" rx="6" fill="url(#billBot)" stroke="#166534" stroke-width="1.5"/>
    <text x="100" y="148" text-anchor="middle" font-family="Arial, sans-serif" font-weight="bold" font-size="36" fill="#14532d" opacity="0.6">$</text>
    <circle cx="100" cy="145" r="28" fill="none" stroke="#14532d" stroke-width="1.5" opacity="0.3"/>

    <!-- Middle bill -->
    <rect x="22" y="75" width="150" height="80" rx="6" fill="url(#billMid)" stroke="#166534" stroke-width="1.5"/>
    <text x="100" y="120" text-anchor="middle" font-family="Arial, sans-serif" font-weight="bold" font-size="36" fill="#14532d" opacity="0.6">$</text>
    <circle cx="100" cy="117" r="28" fill="none" stroke="#14532d" stroke-width="1.5" opacity="0.3"/>

    <!-- Top bill -->
    <rect x="19" y="45" width="150" height="80" rx="6" fill="url(#billTop)" stroke="#166534" stroke-width="1.5"/>
    <text x="100" y="92" text-anchor="middle" font-family="Arial, sans-serif" font-weight="bold" font-size="36" fill="#14532d" opacity="0.6">$</text>
    <circle cx="100" cy="89" r="28" fill="none" stroke="#14532d" stroke-width="1.5" opacity="0.3"/>

    <!-- Dollar sign on top -->
    <text x="100" y="82" text-anchor="middle" font-family="Arial, sans-serif" font-weight="900" font-size="22" fill="#166534">$</text>
</svg>
