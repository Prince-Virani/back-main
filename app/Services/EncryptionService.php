<?php

namespace App\Services;

class EncryptionService
{
    public static function encrypt($plainText, $key)
    {
        $ivLength = openssl_cipher_iv_length(config('app.cipher'));
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($plainText, config('app.cipher'), $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt($encryptedData, $key)
    {
        $decoded = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length(config('app.cipher'));
        $iv = substr($decoded, 0, $ivLength);
        $cipher = substr($decoded, $ivLength);
        return openssl_decrypt($cipher, config('app.cipher'), $key, OPENSSL_RAW_DATA, $iv);
    }
}
