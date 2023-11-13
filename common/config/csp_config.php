<?php
$settings_overwrite = [];
if (YII_ENV_DEV) {
    $settings_overwrite = [
        'script-src' => "'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.quilljs.com www.google.com www.gstatic.com",
        'style-src' => "'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.quilljs.com",
    ];
}
return array_merge(
    [
        'default-src' => "*",
        'connect-src' => "'self'",
        'font-src' => "'self' https://fonts.googleapis.com https://fonts.gstatic.com",
        'frame-src' => "'self' www.google.com",
        'img-src' => "'self' data:",
        'object-src' => "'self'",
        'script-src' => "'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.quilljs.com www.google.com www.gstatic.com",
        'style-src' => "'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.quilljs.com",
        'media-src' => "'self'",
        'style-src-elem' => "'self'",

        'manifest-src' => "*",
        'prefetch-src' => "*",
        'form-action' => "*",
        'worker-src' => "*",
    ],
    $settings_overwrite
);
