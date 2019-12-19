<?php

namespace Vipertecpro\Captcha\Captcha\Generator;

use Exception;

class GeneratorWaves extends AbstractGenerator implements GeneratorInterface
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function render($str, $params):string
    {
        $hexBgColor = $params['background'][random_int(0, count($params['background']) - 1)];
        $bgColor    = $this->hexToRgb($hexBgColor);

        $hexColors = $params['colors'][random_int(0, count($params['colors']) - 1)];
        $textColor = $this->hexToRgb($hexColors);

        //Prototype
        $img1 = imagecreatetruecolor($params['width'], $params['height']);
        imagefill(/** @scrutinizer ignore-type */$img1, 0, 0, imagecolorallocate(/** @scrutinizer ignore-type */$img1, $bgColor['r'], $bgColor['g'], $bgColor['b']));

        //Distorted picture (multi-wave)
        $img2 = imagecreatetruecolor($params['width'], $params['height']);

        //Print text
        $x = ($params['width'] - strlen($str) * ($params['letterSpacing'] + $params['fontSize'] * 0.66)) / 2;
        for ($i = 0, $iMax = strlen($str); $i < $iMax; $i++) {
            ImageTTFtext(
                /** @scrutinizer ignore-type */$img1,
                $params['fontSize'],
                0,
                $x,
                ceil(($params['height'] + $params['fontSize']) / 2),
                imagecolorallocate(/** @scrutinizer ignore-type */$img1, $textColor['r'], $textColor['g'], $textColor['b']),
                $params['font'],
                $str[$i]
            );
            $x += ceil($params['fontSize'] * 0.66) + $params['letterSpacing'];
        }

        //Scratch (text color)
        for ($i = 0; $i < $params['scratches'][0]; $i++) {
            $this->drawScratch(/** @scrutinizer ignore-type */$img1, $params['width'], $params['height'], $hexColors);
        }

        //Scratches (background color)
        for ($i = 0; $i < $params['scratches'][1]; $i++) {
            $this->drawScratch(/** @scrutinizer ignore-type */$img1, $params['width'], $params['height'], $hexBgColor);
        }

        $sxR1 = random_int(7, 10) / 120;
        $syR1 = random_int(7, 10) / 120;
        $sxR2 = random_int(7, 10) / 120;
        $syR2 = random_int(7, 10) / 120;

        $sxF1 = random_int(0, 314) / 100;
        $sxF2 = random_int(0, 314) / 100;
        $syF1 = random_int(0, 314) / 100;
        $syF2 = random_int(0, 314) / 100;

        $sxA = random_int(4, 6);
        $syA = random_int(4, 6);

        for ($x = 0; $x < $params['width']; $x++) {
            for ($y = 0; $y < $params['height']; $y++) {
                $sx = $x + (sin($x * $sxR1 + $sxF1) + sin($y * $sxR2 + $sxF2)) * $sxA;
                $sy = $y + (sin($x * $syR1 + $syF1) + sin($y * $syR2 + $syF2)) * $syA;

                if ($sx < 0 || $sy < 0 || $sx >= $params['width'] - 1 || $sy >= $params['height'] - 1) {
                    $r = $rX = $rY = $rXY = $bgColor['r'];
                    $g = $gX = $gY = $gXY = $bgColor['g'];
                    $b = $bX = $bY = $bXY = $bgColor['b'];
                } else {
                    $rgb = imagecolorat(/** @scrutinizer ignore-type */$img1, $sx, $sy);
                    $r   = ($rgb >> 16) & 0xFF;
                    $g   = ($rgb >> 8) & 0xFF;
                    $b   = $rgb & 0xFF;

                    $rgb = imagecolorat(/** @scrutinizer ignore-type */$img1, $sx + 1, $sy);
                    $rX  = ($rgb >> 16) & 0xFF;
                    $gX  = ($rgb >> 8) & 0xFF;
                    $bX  = $rgb & 0xFF;

                    $rgb = imagecolorat(/** @scrutinizer ignore-type */$img1, $sx, $sy + 1);
                    $rY  = ($rgb >> 16) & 0xFF;
                    $gY  = ($rgb >> 8) & 0xFF;
                    $bY  = $rgb & 0xFF;

                    $rgb = imagecolorat(/** @scrutinizer ignore-type */$img1, $sx + 1, $sy + 1);
                    $rXY = ($rgb >> 16) & 0xFF;
                    $gXY = ($rgb >> 8) & 0xFF;
                    $bXY = $rgb & 0xFF;
                }

                if (
                    $r === $rX &&
                    $r === $rY &&
                    $r === $rXY &&
                    $g === $gX &&
                    $g === $gY &&
                    $g === $gXY &&
                    $b === $bX &&
                    $b === $bY &&
                    $b === $bXY
                ) {
                    if ($r === $bgColor['r'] && $g === $bgColor['g'] && $b === $bgColor['b']) {
                        $newR = $bgColor['r'];
                        $newG = $bgColor['g'];
                        $newB = $bgColor['b'];
                    } else {
                        $newR = $textColor['r'];
                        $newG = $textColor['g'];
                        $newB = $textColor['b'];
                    }
                } else {
                    $frsx  = $sx - floor($sx);
                    $frsy  = $sy - floor($sy);
                    $frsx1 = 1 - $frsx;
                    $frsy1 = 1 - $frsy;

                    $newR = floor($r * $frsx1 * $frsy1 +
                        $rX * $frsx * $frsy1 +
                        $rY * $frsx1 * $frsy +
                        $rXY * $frsx * $frsy);
                    $newG = floor($g * $frsx1 * $frsy1 +
                        $gX * $frsx * $frsy1 +
                        $gY * $frsx1 * $frsy +
                        $gXY * $frsx * $frsy);
                    $newB = floor($b * $frsx1 * $frsy1 +
                        $bX * $frsx * $frsy1 +
                        $bY * $frsx1 * $frsy +
                        $bXY * $frsx * $frsy);
                }
                imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newR, $newG, $newB));
            }
        }
        ob_start();
        imagepng($img2);
        $content = ob_get_clean();
        imagedestroy($img1);
        imagedestroy($img2);
        return $content;
    }
}
