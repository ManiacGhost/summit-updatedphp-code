<?php

return [
    /*
    |--------------------------------------------------------------------------
    | S3 Presigned URL Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the expiration time and behavior for S3 presigned URLs
    |
    */

    'presigned_url_expiration_minutes' => env('S3_PRESIGNED_URL_EXPIRATION', 60),

    /*
    |--------------------------------------------------------------------------
    | Enable Image URL Conversion
    |--------------------------------------------------------------------------
    |
    | Automatically convert S3 image URLs to presigned URLs in API responses
    |
    */
    'auto_convert_image_urls' => env('S3_AUTO_CONVERT_IMAGES', true),
];
