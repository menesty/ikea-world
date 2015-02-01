<?php

/**
 * User: Menesty
 * Date: 2/1/15
 * Time: 22:19
 */
class PasswordService
{
    private $passwordKey;

    public function __construct($passwordKey)
    {
        $this->passwordKey = $passwordKey;
    }

    public function encrypt($input)
    {
        return hash('sha512', $this->passwordKey . $input . $this->passwordKey);
    }

    public function generatePassword()
    {
        return $this->encrypt($this->generatePasswordString());
    }

    public function isValid($password, $hash)
    {
        return $hash == $this->encrypt($password);
    }

    private function generatePasswordString($length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}


