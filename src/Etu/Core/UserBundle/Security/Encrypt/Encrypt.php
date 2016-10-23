<?php

namespace Etu\Core\UserBundle\Security\Encrypt;

/**
 * Encrypt passwords using the parameters secret.
 *
 * @author Titouan
 */
class Encrypt
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @param $text
     *
     * @return string
     */
    public function encrypt($text)
    {
        $count = 0;
        $result = '';

        for ($i = 0; $i < strlen($text); ++$i) {
            if ($count == strlen($this->key)) {
                $count = 0;
            }

            $result .= substr($this->key, $count, 1).(substr($text, $i, 1) ^ substr($this->key, $count, 1));
            ++$count;
        }

        return base64_encode($this->generateKey($result, $this->key));
    }

    /**
     * @param $text
     *
     * @return string
     */
    public function decrypt($text)
    {
        $text = $this->generateKey(base64_decode($text), $this->key);
        $result = '';

        for ($i = 0; $i < strlen($text); ++$i) {
            $md5 = substr($text, $i, 1);
            ++$i;
            $result .= (substr($text, $i, 1) ^ $md5);
        }

        return $result;
    }

    /**
     * @param $text
     * @param $encryptKey
     *
     * @return string
     */
    private function generateKey($text, $encryptKey)
    {
        $encryptKey = md5($encryptKey);
        $count = 0;
        $result = '';

        for ($i = 0; $i < strlen($text); ++$i) {
            if ($count == strlen($encryptKey)) {
                $count = 0;
            }

            $result .= substr($text, $i, 1) ^ substr($encryptKey, $count, 1);
            ++$count;
        }

        return $result;
    }
}
