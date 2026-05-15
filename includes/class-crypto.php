<?php
if (!defined('ABSPATH')) exit;

class Causabi_Crypto {
    private static function get_key(): string {
        return hash('sha256', wp_salt('auth') . wp_salt('secure_auth'), true);
    }

    public static function encrypt(string $value): string {
        if (empty($value)) return '';
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', self::get_key(), 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt(string $value): string {
        if (empty($value)) return '';
        $data = base64_decode($value);
        if (strlen($data) <= 16) return $value; // fallback: unencrypted old value
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        $result = openssl_decrypt($encrypted, 'AES-256-CBC', self::get_key(), 0, $iv);
        return $result !== false ? $result : '';
    }
}
