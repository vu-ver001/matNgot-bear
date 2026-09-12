@php
    $icons = [
        'package' => 'fa-solid fa-box',
        'heart'   => 'fa-solid fa-heart',
        'star'    => 'fa-solid fa-star',
        'message' => 'fa-solid fa-comments',
        'user'    => 'fa-solid fa-user',
        'lock'    => 'fa-solid fa-lock',
        'logout'  => 'fa-solid fa-arrow-right-from-bracket',
        'menu'    => 'fa-solid fa-bars',
    ];
    $iconClass = $icons[$name] ?? (str_starts_with($name ?? '', 'fa-') ? $name : 'fa-solid fa-circle');
@endphp
<i class="{{ $iconClass }} account-icon" aria-hidden="true"></i>
