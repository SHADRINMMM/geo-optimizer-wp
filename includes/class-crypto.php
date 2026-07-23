<?php
if (!defined('ABSPATH')) exit;

class Causabi_Crypto {
    private static function get_key(): string {
        return hash('sha256', wp_salt('auth') . wp_salt('secure_auth'), true);
    }

    // Idempotent: encrypting an already-encrypted blob returns it unchanged
    // instead of double-wrapping it. WordPress core's register_setting()
    // Settings API calls sanitize_callback twice on the very first save
    // (update_option() -> add_option() -> sanitize_option() again) — see
    // lessons_wp_sanitize_callback_not_idempotent memory. Without this guard
    // every first-time key save silently corrupts the key.
    public static function encrypt(string $value): string {
        if (empty($value)) return '';
        if (self::looks_encrypted($value)) return $value;
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', self::get_key(), 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt(string $value): string {
        if (empty($value)) return '';
        $data = base64_decode($value, true);
        if ($data === false || strlen($data) <= 16) return $value; // fallback: unencrypted old value
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        $result = openssl_decrypt($encrypted, 'AES-256-CBC', self::get_key(), 0, $iv);
        return $result !== false ? $result : '';
    }

    // True if $value decrypts to something plausible with our key — i.e. it
    // is already an encrypt() output, not raw plaintext (an API key).
    private static function looks_encrypted(string $value): bool {
        $data = base64_decode($value, true);
        if ($data === false || strlen($data) <= 16) return false;
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        $result = @openssl_decrypt($encrypted, 'AES-256-CBC', self::get_key(), 0, $iv);
        return $result !== false && $result !== '';
    }

    // Single entry point for reading the stored API key. Detects and repairs
    // pre-1.2.2 double-encrypted values in place (re-saves singly-encrypted),
    // so every other read site sees a plain, correct key going forward.
    public static function get_api_key(): string {
        $stored = get_option('causabi_api_key', '');
        if (empty($stored)) return '';

        $once = self::decrypt($stored);
        if ($once !== '' && self::looks_encrypted($once)) {
            $raw = self::decrypt($once);
            if ($raw !== '') {
                update_option('causabi_api_key', self::encrypt($raw));
            }
            return $raw;
        }
        return $once;
    }
}
