@php
    $map = [
        'active'         => ['class' => 'badge-success',   'label' => 'نشط'],
        'inactive'       => ['class' => 'badge-secondary', 'label' => 'غير نشط'],
        'suspended'      => ['class' => 'badge-warning',   'label' => 'موقوف'],
        'terminated'     => ['class' => 'badge-danger',    'label' => 'منتهي'],
        'available'      => ['class' => 'badge-success',   'label' => 'متاح'],
        'assigned'       => ['class' => 'badge-info',      'label' => 'مُعيَّن'],
        'maintenance'    => ['class' => 'badge-warning',   'label' => 'صيانة'],
        'retired'        => ['class' => 'badge-dark',      'label' => 'مسحوب'],
        'valid'          => ['class' => 'badge-success',   'label' => 'ساري'],
        'expiring_soon'  => ['class' => 'badge-warning',   'label' => 'ينتهي قريباً'],
        'expired'        => ['class' => 'badge-danger',    'label' => 'منتهي الصلاحية'],
        'delegate'       => ['class' => 'badge-primary',   'label' => 'مندوب'],
        'vehicle'        => ['class' => 'badge-info',      'label' => 'مركبة'],
        'company'        => ['class' => 'badge-secondary', 'label' => 'الشركة'],
        'true'           => ['class' => 'badge-success',   'label' => 'نشط'],
        'false'          => ['class' => 'badge-secondary', 'label' => 'غير نشط'],
        '1'              => ['class' => 'badge-success',   'label' => 'نشط'],
        '0'              => ['class' => 'badge-secondary', 'label' => 'غير نشط'],
    ];

    $key    = (string) ($status ?? '');
    $config = $map[$key] ?? ['class' => 'badge-secondary', 'label' => $key];
@endphp

<span class="badge {{ $config['class'] }}">{{ $config['label'] }}</span>
