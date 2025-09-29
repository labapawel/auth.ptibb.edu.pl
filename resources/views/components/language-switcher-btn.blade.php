@props([
    'languages' => [
        ['code' => 'pl', 'label' => 'Polski', 'flag' => 'pl'],
        ['code' => 'en', 'label' => 'English', 'flag' => 'gb'],
        ['code' => 'uk', 'label' => 'Українська', 'flag' => 'ua'],
    ],
])

@php
    $current = app()->getLocale();
    $baseClasses = 'inline-flex w-10 h-10 items-center justify-center rounded transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 overflow-hidden';
    $activeClasses = 'bg-blue-600 ring-2 ring-indigo-300 shadow-lg scale-110';
    $inactiveClasses = 'bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 shadow-md hover:scale-105';
@endphp

@foreach ($languages as $lang)
    <a href="{{ url('lang/' . $lang['code']) }}"
       class="{{ $baseClasses }} {{ $current === $lang['code'] ? $activeClasses : $inactiveClasses }}"
       aria-label="{{ $lang['label'] }}" title="{{ $lang['label'] }}">
        <span class="fi fi-{{ $lang['flag'] }} text-lg"></span>
    </a>
@endforeach