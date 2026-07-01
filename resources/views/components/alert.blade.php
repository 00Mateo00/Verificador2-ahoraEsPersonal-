@props([
    'type' => 'info', // success, error, warning, info, danger
    'title' => null,
])

@php
    $styles = [
        'success' => [
            'bg' => '#d4edda',
            'border' => '#c3e6cb',
            'text' => '#155724',
            'title_color' => '#155724',
            'icon' => ''
        ],
        'error' => [
            'bg' => '#f8d7da',
            'border' => '#f5c6cb',
            'text' => '#721c24',
            'title_color' => '#721c24',
            'icon' => '⚠️'
        ],
        'danger' => [
            'bg' => '#fff1f2',
            'border' => '#fecdd3',
            'text' => '#be123c',
            'title_color' => '#9f1239',
            'icon' => '⚠️'
        ],
        'warning' => [
            'bg' => '#fffbeb',
            'border' => '#fef3c7',
            'text' => '#b45309',
            'title_color' => '#92400e',
            'icon' => '⚠️'
        ],
        'info' => [
            'bg' => '#eff6ff',
            'border' => '#bfdbfe',
            'text' => '#1e3a8a',
            'title_color' => '#1e40af',
            'icon' => '🔒'
        ],
        'neutral' => [
            'bg' => '#fffbeb',
            'border' => '#fef3c7',
            'text' => '#b45309',
            'title_color' => '#92400e',
            'icon' => ''
        ]
    ][$type] ?? [
        'bg' => '#f1f5f9',
        'border' => '#cbd5e1',
        'text' => '#334155',
        'title_color' => '#0f172a',
        'icon' => 'ℹ️'
    ];
@endphp

<div style="background-color: {{ $styles['bg'] }}; border: 1px solid {{ $styles['border'] }}; border-radius: 8px; padding: 20px; margin-bottom: 25px; display: flex; align-items: flex-start; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
    <span style="font-size: 1.5rem; line-height: 1;">{{ $styles['icon'] }}</span>
    <div>
        @if($title)
            <strong style="color: {{ $styles['title_color'] }}; font-size: 1rem; display: block; margin-bottom: 4px;">{{ $title }}</strong>
        @endif
        <p style="color: {{ $styles['text'] }}; font-size: 0.85rem; margin: 0; line-height: 1.5;">
            {{ $slot }}
        </p>
    </div>
</div>