<?php namespace Services;

use Config\CryptServiceConfig as Config;

class CryptService
{
    public static function encrypt(string $string): string
    {
        $string = openssl_encrypt(
            $string, Config::get('algorythm'), Config::get('passphrase'), OPENSSL_RAW_DATA, Config::get('initvector')
        );

        if ( $string === false ) {
            return _APP_EMPTY_STRING_;
        }

        return base64_encode($string);
    }

    public static function decrypt(string $string): string
    {
        $string = openssl_decrypt(
            base64_decode($string), Config::get('algorythm'), Config::get('passphrase'), OPENSSL_RAW_DATA, Config::get('initvector')
        );

        if ( $string === false ) {
            return _APP_EMPTY_STRING_;
        }

        return $string;
    }
}
