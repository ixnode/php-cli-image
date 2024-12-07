<?php

/*
 * This file is part of the ixnode/php-cli-image project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ixnode\PhpCliImage\Utils;

use ImagickPixel;
use ImagickPixelException;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use UnexpectedValueException;

/**
 * Class Color
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-08-13)
 * @since 0.1.0 (2023-08-13) First version.
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Color
{
    final public const PRECISION_NONE = -1;

    final public const VALUE_HASH = '#';

    final public const CONVERT_0_03928 = .03928;

    /* @see http://www.brucelindbloom.com/index.html?Eqn_RGB_XYZ_Matrix.html (sRGB → XYZ) */
    final public const MATRIX_SRGB_XYZ = [
        [.4124564, .3575761, .1804375, ],
        [.2126729, .7151522, .0721750, ],
        [.0193339, .1191920, .9503041, ],
    ];

    /* @see https://en.wikipedia.org/wiki/SRGB */
    final public const COLOR_INDEX_RGB_RED = 'r';
    final public const COLOR_VALUE_RGB_RED_MIN = 0;
    final public const COLOR_VALUE_RGB_RED_MAX = 255;
    final public const COLOR_INDEX_RGB_GREEN = 'g';
    final public const COLOR_VALUE_RGB_GREEN_MIN = 0;
    final public const COLOR_VALUE_RGB_GREEN_MAX = 255;
    final public const COLOR_INDEX_RGB_BLUE = 'b';
    final public const COLOR_VALUE_RGB_BLUE_MIN = 0;
    final public const COLOR_VALUE_RGB_BLUE_MAX = 255;

    final public const COLORS_RGB = [
        self::COLOR_INDEX_RGB_RED,
        self::COLOR_INDEX_RGB_GREEN,
        self::COLOR_INDEX_RGB_BLUE,
    ];

    /* @see https://en.wikipedia.org/wiki/SRGB */
    final public const COLOR_INDEX_SRGB_RED = 'r';
    final public const COLOR_VALUE_SRGB_RED_MIN = 0.;
    final public const COLOR_VALUE_SRGB_RED_MAX = 1.;
    final public const COLOR_INDEX_SRGB_GREEN = 'g';
    final public const COLOR_VALUE_SRGB_GREEN_MIN = 0.;
    final public const COLOR_VALUE_SRGB_GREEN_MAX = 1.;
    final public const COLOR_INDEX_SRGB_BLUE = 'b';
    final public const COLOR_VALUE_SRGB_BLUE_MIN = 0.;
    final public const COLOR_VALUE_SRGB_BLUE_MAX = 1.;

    final public const COLORS_SRGB = [
        self::COLOR_INDEX_SRGB_RED,
        self::COLOR_INDEX_SRGB_GREEN,
        self::COLOR_INDEX_SRGB_BLUE,
    ];

    /* @see https://en.wikipedia.org/wiki/CIELAB_color_space */
    final public const COLOR_INDEX_LAB_LIGHTNESS = 'L';
    final public const COLOR_VALUE_LAB_LIGHTNESS_MIN = 0.;
    final public const COLOR_VALUE_LAB_LIGHTNESS_MAX = 100.;
    final public const COLOR_INDEX_LAB_A = 'a';
    final public const COLOR_VALUE_LAB_A_MIN = -128.;
    final public const COLOR_VALUE_LAB_A_MAX = 127.;
    final public const COLOR_INDEX_LAB_B = 'b';
    final public const COLOR_VALUE_LAB_B_MIN = -128.;
    final public const COLOR_VALUE_LAB_B_MAX = 127.;

    final public const COLORS_LAB = [
        self::COLOR_INDEX_LAB_LIGHTNESS,
        self::COLOR_INDEX_LAB_A,
        self::COLOR_INDEX_LAB_B,
    ];

    /* @see https://en.wikipedia.org/wiki/CIE_1931_color_space */
    final public const COLOR_INDEX_XYZ_X = 'x';
    final public const COLOR_VALUE_XYZ_X_MIN = 0.;
    final public const COLOR_VALUE_XYZ_X_MAX = 1.;
    final public const COLOR_INDEX_XYZ_Y = 'y';
    final public const COLOR_VALUE_XYZ_Y_MIN = 0.;
    final public const COLOR_VALUE_XYZ_Y_MAX = 1.;
    final public const COLOR_INDEX_XYZ_Z = 'z';
    final public const COLOR_VALUE_XYZ_Z_MIN = 0.;
    final public const COLOR_VALUE_XYZ_Z_MAX = 1.;

    final public const COLORS_XYZ = [
        self::COLOR_INDEX_XYZ_X,
        self::COLOR_INDEX_XYZ_Y,
        self::COLOR_INDEX_XYZ_Z,
    ];

    /**
     * Single color value: Converts given rgb value to hex.
     *
     * @param int $value
     * @param bool $lowercase
     * @return string
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function convertRgbToHex(int $value, bool $lowercase = false): string
    {
        $colorHex = sprintf('%02X', $value);

        if (!$lowercase) {
            return $colorHex;
        }

        return strtolower($colorHex);
    }

    /**
     * Single color value: Converts given rgb value to srgb.
     *
     * @param int $value
     * @param int $precision
     * @return float
     */
    public static function convertRgbToSrgb(int $value, int $precision = self::PRECISION_NONE): float
    {
        $value /= 255;

        $srgb = $value <= self::CONVERT_0_03928 ? $value / 12.92 : (($value + .055) / 1.055) ** 2.4;

        if ($precision === self::PRECISION_NONE) {
            return $srgb;
        }

        return round($srgb, $precision);
    }

    /**
     * Single color value: Converts given xyz value to Lab value.
     *
     * @param float $value
     * @param int $precision
     * @return float
     */
    public static function convertXyzToLab(float $value, int $precision = -1): float
    {
        $lab = $value > 216 / 24389 ? $value ** (1 / 3) : 841 * $value / 108 + 4 / 29;

        if ($precision === self::PRECISION_NONE) {
            return $lab;
        }

        return round($lab, $precision);
    }

    /**
     * Full color value: Converts given rgb integer values to integer.
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @return int
     */
    #[Pure]
    public static function convertRgbsToInt(int $red, int $green, int $blue): int
    {
        return $red * 256 * 256 + $green * 256 + $blue;
    }

    /**
     * Full color value: Converts given rgb integer values to hex value.
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param bool $prependHash = true
     * @param bool $lowercase
     * @return string
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    #[Pure]
    public static function convertRgbsToHex(int $red, int $green, int $blue, bool $prependHash = true, bool $lowercase = false): string
    {
        $colorHex = self::convertIntToHex($red * 256 * 256 + $green * 256 + $blue, $prependHash);

        if (!$lowercase) {
            return $colorHex;
        }

        return strtolower($colorHex);
    }

    /**
     * Full color value: Converts given integer to hex value.
     *
     * Examples:
     * 255 → #0000FF
     * 255*256*256 + 255*256 + 255 → #FFFFFF
     * 128*256*256 + 0*256 + 128 → #800080
     *
     * @param int $color
     * @param bool $prependHash = true
     * @param bool $lowercase
     * @return string
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function convertIntToHex(int $color, bool $prependHash = true, bool $lowercase = false): string
    {
        $colorHex = ($prependHash ? self::VALUE_HASH : '').sprintf('%06X', $color);

        if (!$lowercase) {
            return $colorHex;
        }

        return strtolower($colorHex);
    }

    /**
     * Full color value: Converts given ImagickPixel to hex value.
     *
     * @param ImagickPixel $imagickPixel
     * @param bool $prependHash
     * @param bool $lowercase
     * @return string
     * @throws ImagickPixelException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function convertImagickPixelToHex(ImagickPixel $imagickPixel, bool $prependHash = true, bool $lowercase = false): string
    {
        $color = $imagickPixel->getColor();

        $colorHex = match ($prependHash) {
            true => sprintf("#%02x%02x%02x", $color['r'], $color['g'], $color['b']),
            default => sprintf("%02x%02x%02x", $color['r'], $color['g'], $color['b']),
        };

        if (!$lowercase) {
            return $colorHex;
        }

        return strtolower($colorHex);
    }

    /**
     * Full color value: Converts given integer into rgb array.
     *
     * @param int $color
     * @return array{r:int, g:int, b:int}
     */
    #[ArrayShape([self::COLOR_INDEX_RGB_RED => "int", self::COLOR_INDEX_RGB_GREEN => "int", self::COLOR_INDEX_RGB_BLUE => "int"])]
    public static function convertIntToRgbArray(int $color): array
    {
        return [
            self::COLOR_INDEX_RGB_RED => $color >> 16 & 0xFF,
            self::COLOR_INDEX_RGB_GREEN => $color >> 8 & 0xFF,
            self::COLOR_INDEX_RGB_BLUE => $color & 0xFF,
        ];
    }

    /**
     * Converts int color to lab array.
     *
     * @param int $color
     * @param int $precision
     * @return array{L: float, a: float, b: float}
     */
    #[ArrayShape([self::COLOR_INDEX_LAB_LIGHTNESS => "float", self::COLOR_INDEX_LAB_A => "float", self::COLOR_INDEX_LAB_B => "float"])]
    public static function convertIntToLabArray(int $color, int $precision = -1): array
    {
        return self::convertXyzArrayToLabArray(
            self::convertSrgbArrayToXyzArray(
                self::convertRgbArrayToSrgbArray(
                    self::convertIntToRgbArray($color)
                )
            ),
            $precision
        );
    }

    /**
     * Full color value: Converts given hex value to integer.
     *
     * Examples:
     * #800080 → 128*256*256 + 0*256 + 128
     *
     * @param string $color
     * @return int
     */
    public static function convertHexToInt(string $color): int
    {
        return intval(hexdec(ltrim($color, self::VALUE_HASH)));
    }

    /**
     * Converts given hex value to integer.
     *
     * Examples:
     * #800080 → 128*256*256 + 0*256 + 128
     *
     * @param string $color
     * @return array{r:int, g:int, b:int}
     */
    #[Pure]
    #[ArrayShape(['r' => "int", 'g' => "int", 'b' => "int"])]
    public static function convertHexToRgbArray(string $color): array
    {
        return self::convertIntToRgbArray(self::convertHexToInt($color));
    }

    /**
     * Converts given rgb array into integer.
     *
     * @param array{r:int, g:int, b:int} $rgb
     * @return int
     * @throws InvalidArgumentException
     */
    public static function convertRgbArrayToInt(array $rgb): int
    {
        self::checkRgb($rgb);

        return ($rgb[self::COLOR_INDEX_RGB_RED] * 256 * 256) + ($rgb[self::COLOR_INDEX_RGB_GREEN] * 256) + ($rgb[self::COLOR_INDEX_RGB_BLUE]);
    }

    /**
     * Converts given rgb array into integer.
     *
     * @param array{r:int, g:int, b:int} $rgb
     * @param bool $prependHash
     * @param bool $lowercase
     * @return string
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function convertRgbArrayToHex(array $rgb, bool $prependHash = true, bool $lowercase = false): string
    {
        return self::convertIntToHex(self::convertRgbArrayToInt($rgb), $prependHash, $lowercase);
    }

    /**
     * Converts given rgb array to srgb array.
     *
     * @param array{r: int, g: int, b: int} $rgb
     * @return array{r: float, g: float, b: float}
     */
    #[ArrayShape([self::COLOR_INDEX_SRGB_RED => "float", self::COLOR_INDEX_SRGB_GREEN => "float", self::COLOR_INDEX_SRGB_BLUE => "float"])]
    public static function convertRgbArrayToSrgbArray(array $rgb, int $precision = -1): array
    {
        self::checkRgb($rgb);

        return [
            self::COLOR_INDEX_SRGB_RED => self::convertRgbToSrgb($rgb[self::COLOR_INDEX_SRGB_RED], $precision),
            self::COLOR_INDEX_SRGB_GREEN => self::convertRgbToSrgb($rgb[self::COLOR_INDEX_SRGB_GREEN], $precision),
            self::COLOR_INDEX_SRGB_BLUE => self::convertRgbToSrgb($rgb[self::COLOR_INDEX_SRGB_BLUE], $precision),
        ];
    }

    /**
     * Converts given srgb array into xyz array.
     *
     * @param array{r: float, g: float, b: float} $srgb
     * @param int $precision
     * @return array{x: float, y: float, z: float}
     */
    #[ArrayShape([self::COLOR_INDEX_XYZ_X => "float", self::COLOR_INDEX_XYZ_Y => "float", self::COLOR_INDEX_XYZ_Z => "float"])]
    public static function convertSrgbArrayToXyzArray(array $srgb, int $precision = -1): array
    {
        self::checkSrgb($srgb);

        [$xValue, $yValue, $zValue, ] = self::matrixVectorMultiplication(self::MATRIX_SRGB_XYZ, $srgb, $precision);

        return [
            self::COLOR_INDEX_XYZ_X => $xValue,
            self::COLOR_INDEX_XYZ_Y => $yValue,
            self::COLOR_INDEX_XYZ_Z => $zValue,
        ];
    }

    /**
     * Converts xyz array to lab array.
     *
     * @param array{x: float, y: float, z: float} $xyz
     * @param int $precision
     * @return array{L: float, a: float, b: float}
     */
    #[Pure]
    #[ArrayShape([self::COLOR_INDEX_LAB_LIGHTNESS => "float", self::COLOR_INDEX_LAB_A => "float", self::COLOR_INDEX_LAB_B => "float"])]
    public static function convertXyzArrayToLabArray(array $xyz, int $precision = -1): array
    {
        /* http://en.wikipedia.org/wiki/Illuminant_D65#Definition */
        $xnValue = .95047;
        $ynValue = 1;
        $znValue = 1.08883;

        /* http://en.wikipedia.org/wiki/Lab_color_space#CIELAB-CIEXYZ_conversions */
        $lightness = floatval(116 * self::convertXyzToLab($xyz[self::COLOR_INDEX_XYZ_Y] / $ynValue) - 16);
        $aValue = floatval(500 * (self::convertXyzToLab($xyz[self::COLOR_INDEX_XYZ_X] / $xnValue) - self::convertXyzToLab($xyz[self::COLOR_INDEX_XYZ_Y] / $ynValue)));
        $bValue = floatval(200 * (self::convertXyzToLab($xyz[self::COLOR_INDEX_XYZ_Y] / $ynValue) - self::convertXyzToLab($xyz[self::COLOR_INDEX_XYZ_Z] / $znValue)));

        $lightness = self::correctRangeFloat($lightness, self::COLOR_VALUE_LAB_LIGHTNESS_MIN, self::COLOR_VALUE_LAB_LIGHTNESS_MAX);
        $aValue = self::correctRangeFloat($aValue, self::COLOR_VALUE_LAB_A_MIN, self::COLOR_VALUE_LAB_A_MAX);
        $bValue = self::correctRangeFloat($bValue, self::COLOR_VALUE_LAB_B_MIN, self::COLOR_VALUE_LAB_B_MAX);

        if ($precision === self::PRECISION_NONE) {
            return [
                self::COLOR_INDEX_LAB_LIGHTNESS => $lightness,
                self::COLOR_INDEX_LAB_A => $aValue,
                self::COLOR_INDEX_LAB_B => $bValue,
            ];
        }

        return [
            self::COLOR_INDEX_LAB_LIGHTNESS => round($lightness, $precision),
            self::COLOR_INDEX_LAB_A => round($aValue, $precision),
            self::COLOR_INDEX_LAB_B => round($bValue, $precision),
        ];
    }

    /**
     * Correct the range of given float value.
     *
     * @param float $value
     * @param float $minValue
     * @param float $maxValue
     * @return float
     */
    protected static function correctRangeFloat(float $value, float $minValue, float $maxValue): float
    {
        $value = floatval(max($value, $minValue));

        return floatval(min($value, $maxValue));
    }

    /**
     * Checks given rgb array.
     *
     * @param array{r:int, g:int, b:int} $rgb
     * @return void
     * @throws InvalidArgumentException
     */
    protected static function checkRgb(array $rgb): void
    {
        foreach (self::COLORS_RGB as $color) {
            if (!array_key_exists($color, $rgb)) {
                throw new InvalidArgumentException(sprintf('Missing color index "%s" (%s:%d).', $color, __FILE__, __LINE__));
            }
        }

        foreach (self::COLORS_RGB as $color) {
            if (!is_int($rgb[$color])) {
                throw new UnexpectedValueException(sprintf('Unexpected value format given for color "%s". Integer expected (%s:%d).', $color, __FILE__, __LINE__));
            }
        }
    }

    /**
     * Checks given srgb array.
     *
     * @param array{r:float, g:float, b:float} $srgb
     * @return void
     * @throws InvalidArgumentException
     */
    protected static function checkSrgb(array $srgb): void
    {
        foreach (self::COLORS_SRGB as $color) {
            if (!array_key_exists($color, $srgb)) {
                throw new InvalidArgumentException(sprintf('Missing color index "%s" (%s:%d).', $color, __FILE__, __LINE__));
            }
        }

        foreach (self::COLORS_SRGB as $color) {
            if (!is_float($srgb[$color])) {
                throw new UnexpectedValueException(sprintf('Unexpected value format given for color "%s". Float expected (%s:%d).', $color, __FILE__, __LINE__));
            }
        }
    }

    /**
     * Does a matrix vector multiplication.
     *
     * @param array<array<int, float>> $matrix
     * @param array<string, float> $vector
     * @param int $precision
     * @return array<int, float>
     */
    protected static function matrixVectorMultiplication(array $matrix, array $vector, int $precision = -1): array
    {
        $keys = array_keys($vector);

        $xValue1 = $vector[$keys[0]];
        $xValue2 = $vector[$keys[1]];
        $xValue3 = $vector[$keys[2]];

        $yValue1 = floatval($matrix[0][0] * $xValue1 + $matrix[0][1] * $xValue2 + $matrix[0][2] * $xValue3);
        $yValue2 = floatval($matrix[1][0] * $xValue1 + $matrix[1][1] * $xValue2 + $matrix[1][2] * $xValue3);
        $yValue3 = floatval($matrix[2][0] * $xValue1 + $matrix[2][1] * $xValue2 + $matrix[2][2] * $xValue3);

        if ($precision === self::PRECISION_NONE) {
            return [$yValue1, $yValue2, $yValue3, ];
        }

        return [round($yValue1, $precision), round($yValue2, $precision), round($yValue3, $precision), ];
    }
}
