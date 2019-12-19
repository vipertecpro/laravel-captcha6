<?php

namespace Vipertecpro\Captcha\Captcha\Generator;

use Exception;

abstract class AbstractGenerator
{
    /**
     * Color converter cache.
     *
     * @var array
     */
    private $colorCache = [];

    /**
     * Color converter - HEX to RGB.
     *
     * @param string $hex Color.
     * @return array
     */
    protected function hexToRgb($hex)
    {
        if (! isset($this->colorCache[$hex])) {
            $this->colorCache[$hex] = [
                'r' => hexdec(substr($hex, 0, 2)),
                'g' => hexdec(substr($hex, 2, 2)),
                'b' => hexdec(substr($hex, 4, 2))
            ];
        }

        return $this->colorCache[$hex];
    }

    /**
     * Draw scratches.
     *
     * @param resource $img
     * @param int $imageWidth
     * @param int $imageHeight
     * @param string $hex
     * @throws Exception
     */
    protected function drawScratch($img, $imageWidth, $imageHeight, $hex): void
    {
        $rgb = $this->hexToRgb($hex);

        imageline(
            $img,
            random_int(0, floor($imageWidth / 2)),
            random_int(1, $imageHeight),
            random_int(floor($imageWidth / 2), $imageWidth),
            random_int(1, $imageHeight),
            imagecolorallocate($img, $rgb['r'], $rgb['g'], $rgb['b'])
        );
    }
}
