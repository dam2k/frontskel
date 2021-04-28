<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel\Utils;

Class StringCrypt
{
    /**
     * XOR scramble a given string with the given key
     * @param string $str The string to be scrambled
     * @param string $key The scramble key
     * @return string
     */
    private static function xor_scramble(string $str, string $key): string {
        $enc="";
        for($i=0;$i<strlen($str);$i++){
            $j=$i%strlen($key);
            $enc.=$str[$i]^$key[$j];
        }
        return $enc;
    }

    /**
     * Encrypt the $plaintext string with the given $cipher. The $key is salted with $salt and hashed with $hashalg.
     * Also, the TAG and IV are XOREd scrambled with the salt, so that it's impossible to understand the resulting string
     */
    public static function encryptString(string $plaintext, string $cipher, string $hashalg, string $key, string $salt, bool $b64, bool $compress): string {
        $options=0; $tag="";
        if(!in_array($cipher, openssl_get_cipher_methods())) throw new \Exception("requested ciphersuite not known: " . $cipher);
        // hash the key
        $keyhash=openssl_digest($salt.$key, $hashalg, true);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));

        if($compress) { // you want it deflated
            $plaintext=gzdeflate($plaintext, 9);
        }
        
        $ciphertext = openssl_encrypt($plaintext, $cipher, $keyhash, $options, $iv, $tag);
        if(!$ciphertext) throw new \Exception("failed to encrypt string. Check your keys or string altered!");
        /*
        echo "ciphertext: $ciphertext\n";
        echo "iv: " . base64_encode($iv)."\n";
        echo "xored iv: " . base64_encode(xor_scramble($iv, $salt))."\n";
        echo "tag: " . base64_encode($tag)."\n";
        echo "xored tag: " . base64_encode(xor_scramble($tag, $salt))."\n";
        */
        
        $crypted=self::xor_scramble($iv,$salt).$ciphertext.':'.self::xor_scramble($tag,$salt);
        if($compress) { // you want it deflated
            $crypted=gzdeflate($crypted, 9);
        }
        if($b64) { // you want it BASE64
            $crypted=base64_encode($crypted);
        }
        
        return $crypted;
    }

    /**
     * Decrypt the string previously encrypted with encryptString(). Use the same parameters used to crypt or... epic fail!
     */
    public static function decryptString(string $crypted, string $cipher, string $hashalg, string $key, string $salt, bool $b64, bool $compress): string {
        $options=0;
        if(!in_array($cipher, openssl_get_cipher_methods())) throw new \Exception("requested ciphersuite not known: " . $cipher);
        // hash the key
        $keyhash=openssl_digest($salt.$key, $hashalg, true);
        
        $mixed=$crypted;
        if($b64) { // you want it BASE64
            $mixed=base64_decode($mixed);
        }
        if($compress) { // you want it inflated
            $mixed=@gzinflate($mixed, strlen($mixed)*4);
            if(!$mixed) {
                throw new \Exception("Error inflating string. Someone could be doing something nasty!");
            }
        }
        
        $iv=self::xor_scramble(substr($mixed,0,openssl_cipher_iv_length($cipher)),$salt);
        if(!$iv) throw new \Exception("failed to XOR descramble IV. If your keys are correct someone could be doing something nasty!");
        $ciphertext=substr($mixed,openssl_cipher_iv_length($cipher),strpos($mixed,':',openssl_cipher_iv_length($cipher))-strlen($iv));
        if(!$ciphertext) throw new \Exception("failed to XOR descramble Encrypted String. If your keys are correct someone could be doing something nasty!");
        $tag=self::xor_scramble(substr($mixed,strlen($iv)+strlen($ciphertext)+1),$salt);
        if(!$iv) throw new \Exception("failed to XOR descramble TAG. If your keys are correct someone could be doing something nasty!");
        /*
        echo "iv: ".base64_encode($iv)."\n";
        echo "ciphertext: $ciphertext\n";
        echo "tag: ".base64_encode($tag)."\n";
        */
        $plaintext=openssl_decrypt($ciphertext, $cipher, $keyhash, $options, $iv, $tag);
        if(!$plaintext) throw new \Exception("failed to decrypt string. If your keys are correct someone could be doing something nasty!");
        
        if($compress) { // you want it inflated
            $plaintext=@gzinflate($plaintext, strlen($plaintext)*4);
            if(!$mixed) {
                throw new \Exception("Error inflating string. Someone could be doing something nasty!");
            }
        }
        
        return $plaintext;
    }
}
