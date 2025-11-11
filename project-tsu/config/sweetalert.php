<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    */

    'theme' => env('SWEET_ALERT_THEME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | CDN LINK
    |--------------------------------------------------------------------------
    */

    'cdn' => env('SWEET_ALERT_CDN'),

    /*
    |--------------------------------------------------------------------------
    | Always load the sweetalert.all.js
    |--------------------------------------------------------------------------
    */

    'alwaysLoadJS' => env('SWEET_ALERT_ALWAYS_LOAD_JS', false),

    /*
    |--------------------------------------------------------------------------
    | Never load the sweetalert.all.js
    |--------------------------------------------------------------------------
    */

    'neverLoadJS' => env('SWEET_ALERT_NEVER_LOAD_JS', false),

    /*
    |--------------------------------------------------------------------------
    | AutoClose Timer
    |--------------------------------------------------------------------------
    |
    | [PERUBAHAN] Saya ubah default timer dari 5000ms menjadi 3000ms (3 detik)
    |
    */

    'timer' => env('SWEET_ALERT_TIMER', 3000), // <-- DIUBAH

    /*
    |--------------------------------------------------------------------------
    | Width
    |--------------------------------------------------------------------------
    */

    'width' => env('SWEET_ALERT_WIDTH', '24rem'),

    /*
    |--------------------------------------------------------------------------
    | Height Auto
    |--------------------------------------------------------------------------
    */

    'height_auto' => env('SWEET_ALERT_HEIGHT_AUTO', true),

    /*
    |--------------------------------------------------------------------------
    | Padding
    |--------------------------------------------------------------------------
    */

    'padding' => env('SWEET_ALERT_PADDING', '1.25rem'),

    /*
    |--------------------------------------------------------------------------
    | Background
    |--------------------------------------------------------------------------
    */

    'background' => env('SWEET_ALERT_BACKGROUND', '#fff'),

    /*
    |--------------------------------------------------------------------------
    | Animation
    |--------------------------------------------------------------------------
    */

    'animation' => [
        'enable' => env('SWEET_ALERT_ANIMATION_ENABLE', false),
    ],

    'animatecss' => env('SWEET_ALERT_ANIMATECSS', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css'),

    /*
    |--------------------------------------------------------------------------
    | ShowConfirmButton
    |--------------------------------------------------------------------------
    */

    'show_confirm_button' => env('SWEET_ALERT_CONFIRM_BUTTON', true),

    /*
    |--------------------------------------------------------------------------
    | ShowCloseButton
    |--------------------------------------------------------------------------
    */

    'show_close_button' => env('SWEET_ALERT_CLOSE_BUTTON', false),

    /*
    |-----------------------------------------------------------------------
    | Confirm/Cancel Button Text
    |-----------------------------------------------------------------------
    */

    'button_text' => [
        'confirm' => env('SWEET_ALERT_CONFIRM_BUTTON_TEXT', 'OK'),
        'cancel' => env('SWEET_ALERT_CANCEL_BUTTON_TEXT', 'Batal'), // <-- DIUBAH
    ],

    /*
    |--------------------------------------------------------------------------
    | Toast position
    |--------------------------------------------------------------------------
    */

    'toast_position' => env('SWEET_ALERT_TOAST_POSITION', 'top-end'),

    /*
    |--------------------------------------------------------------------------
    | Progress Bar
    |--------------------------------------------------------------------------
    |
    | [PERUBAHAN] Saya aktifkan progress bar agar lebih keren
    |
    */

    'timer_progress_bar' => env('SWEET_ALERT_TIMER_PROGRESS_BAR', true), // <-- DIUBAH

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */

    'middleware' => [

        'autoClose' => env('SWEET_ALERT_MIDDLEWARE_AUTO_CLOSE', false),

        'toast_position' => env('SWEET_ALERT_MIDDLEWARE_TOAST_POSITION', 'top-end'),

        'toast_close_button' => env('SWEET_ALERT_MIDDLEWARE_TOAST_CLOSE_BUTTON', true),

        'timer' => env('SWEET_ALERT_MIDDLEWARE_ALERT_CLOSE_TIME', 6000),

        'auto_display_error_messages' => env('SWEET_ALERT_AUTO_DISPLAY_ERROR_MESSAGES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Class
    |--------------------------------------------------------------------------
    |
    | [PERUBAHAN] Ini adalah bagian terpenting untuk styling kustom Anda
    |
    */

    'customClass' => [

        'container' => env('SWEET_ALERT_CONTAINER_CLASS'),
        'popup' => env('SWEET_ALERT_POPUP_CLASS', 'my-sweetalert-popup'), // <-- DIUBAH
        'header' => env('SWEET_ALERT_HEADER_CLASS'),
        'title' => env('SWEET_ALERT_TITLE_CLASS', 'my-sweetalert-title'), // <-- DIUBAH
        'closeButton' => env('SWEET_ALERT_CLOSE_BUTTON_CLASS'),
        'icon' => env('SWEET_ALERT_ICON_CLASS'),
        'image' => env('SWEET_ALERT_IMAGE_CLASS'),
        'content' => env('SWEET_ALERT_CONTENT_CLASS', 'my-sweetalert-content'), // <-- DIUBAH
        'input' => env('SWEET_ALERT_INPUT_CLASS'),
        'actions' => env('SWEET_ALERT_ACTIONS_CLASS'),
        'confirmButton' => env('SWEET_ALERT_CONFIRM_BUTTON_CLASS', 'my-sweetalert-confirm-button'), // <-- DIUBAH
        'cancelButton' => env('SWEET_ALERT_CANCEL_BUTTON_CLASS', 'my-sweetalert-cancel-button'), // <-- DIUBAH
        'footer' => env('SWEET_ALERT_FOOTER_CLASS'),
    ],

    /*
    |--------------------------------------------------------------------------
    | confirmDelete
    |--------------------------------------------------------------------------
    |
    | [PERUBAHAN] Mengubah style tombol Hapus agar sesuai UI Anda
    |
    */

    'confirm_delete_confirm_button_text' => env('SWEET_ALERT_CONFIRM_DELETE_CONFIRM_BUTTON_TEXT', 'Ya, hapus!'),
    'confirm_delete_confirm_button_color' => env('SWEET_ALERT_CONFIRM_DELETE_CONFIRM_BUTTON_COLOR', '#DC2626'), // <-- Warna Merah (Tailwind red-600)
    'confirm_delete_cancel_button_color' => env('SWEET_ALERT_CONFIRM_DELETE_CANCEL_BUTTON_COLOR', '#E5E7EB'), // <-- Warna Abu-abu (Tailwind gray-200)
    'confirm_delete_cancel_button_text' => env('SWEET_ALERT_CONFIRM_DELETE_CANCEL_BUTTON_TEXT', 'Batal'),
    'confirm_delete_show_cancel_button' => env('SWEET_ALERT_CONFIRM_DELETE_SHOW_CANCEL_BUTTON', true),
    'confirm_delete_show_close_button' => env('SWEET_ALERT_CONFIRM_DELETE_SHOW_CLOSE_BUTTON', false),
    'confirm_delete_icon' => env('SWEET_ALERT_CONFIRM_DELETE_ICON', 'warning'),
    'confirm_delete_show_loader_on_confirm' => env('SWEET_ALERT_CONFIRM_DELETE_SHOW_LOADER_ON_CONFIRM', true),


];
