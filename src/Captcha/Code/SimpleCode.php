<?php

namespace Vipertecpro\Captcha\Captcha\Code;

use Exception;

class SimpleCode implements CodeInterface
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function generate($chars, $minLength, $maxLength)
    {
        $length = random_int($minLength, $maxLength);
        $code   = [];
        for ($i = 0; $i < $length; $i++) {
            $code[] = $chars[random_int(1, mb_strlen($chars) - 1)];
        }
        $code = implode($code);

        return $code;
    }
}
