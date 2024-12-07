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

namespace Ixnode\PhpCliImage\Engine\Base;

use Ixnode\PhpCliImage\CliImage;
use Ixnode\PhpCliImage\Utils\Color;
use Ixnode\PhpCliImage\Utils\Point;
use Ixnode\PhpContainer\File;
use Ixnode\PhpException\Case\CaseUnsupportedException;

/**
 * Class BaseEngine
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2024-12-07)
 * @since 0.1.0 (2024-12-07) First version.
 */
abstract class BaseEngine
{
    protected const NAME_TRANSPARENT = 'transparent';

    /**
     * Returns the ascii representation of the image (string array).
     *
     * @return array<int, string>
     */
    abstract public function getAsciiLines(): array;

    /**
     * Returns the ascii representation of the image (string).
     *
     * @return string
     */
    abstract public function getAsciiString(): string;

    /**
     * Returns the width of the GdImage object.
     *
     * @return int
     */
    abstract public function getWidth(): int;

    /**
     * Returns the height of the GdImage object.
     *
     * @return int
     */
    abstract public function getHeight(): int;

    /**
     * Returns the color code at position.
     *
     * @param int $posX
     * @param int $posY
     * @return string
     */
    abstract public function getColorAt(int $posX, int $posY): string;

    /**
     * @param CliImage $cliImage
     * @param File|string $image
     * @param int $width
     */
    public function __construct(protected CliImage $cliImage, protected File|string $image, protected int $width = 80)
    {
    }

    /**
     * Returns the real color or color of marker.
     *
     * @param int $cell
     * @param int $line
     * @param string $color
     * @param array<string, Point> $points
     * @return string
     */
    private function getColor(int $cell, int $line, string $color, array $points): string
    {
        foreach ($points as $colorPoint => $point) {
            if ($cell === (int) $point->getX() && $line === (int) $point->getY()) {
                return $colorPoint;
            }
        }

        return $color;
    }

    /**
     * Converts given image to string.
     *
     * @return array<int, string>
     * @throws CaseUnsupportedException
     */
    public function convertImageToLines(): array
    {
        $width = $this->getWidth();
        $height = $this->getHeight();

        $points = $this->cliImage->getPoints();

        $lines = [];
        for ($lineY = 0; $lineY < floor($height / 2); $lineY++) {
            $line = '';
            for ($cellX = 0; $cellX < $width; $cellX++) {
                $lineYTop = 2 * $lineY;
                $lineYBottom = 2 * $lineY + 1;

                $colorTop = $this->getColorAt($cellX, $lineYTop);
                $colorBottom = $lineYBottom + 1 <= $height ? $this->getColorAt($cellX, $lineYBottom) : null;

                $line .= $this->get1x2Pixel(
                    $this->getColor($cellX, $lineYTop, $colorTop, $points),
                    $this->getColor($cellX, $lineYBottom, $colorBottom ?? self::NAME_TRANSPARENT, $points),
                );
            }
            $lines[] = sprintf('%s', $line);
        }

        return $lines;
    }

    /**
     * Translate given color.
     *
     * @param string $color
     * @param string $colorTransparent
     * @return string
     * @throws CaseUnsupportedException
     */
    private function translateColor(string $color, string $colorTransparent = '#000000'): string
    {
        if ($color === self::NAME_TRANSPARENT) {
            return self::NAME_TRANSPARENT;
        }

        /* Check transparent colors. */
        $matches = [];
        if (preg_match('~^#[a-f0-9]{1,2}([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})$~i', $color, $matches)) {
            $color = sprintf('#%s%s%s', $matches[1], $matches[2], $matches[3]);
        }

        if (!preg_match('~^#[a-f0-9]{6}$~i', $color)) {
            throw new CaseUnsupportedException(sprintf('Unexpected color given "%s".', $color));
        }

        if ($color === $colorTransparent) {
            return self::NAME_TRANSPARENT;
        }

        return $color;
    }

    /**
     * Prints 1x2 pixel.
     *
     * @param string $colorTop
     * @param string|null $colorBottom
     * @param int $repeat
     * @return string
     * @throws CaseUnsupportedException
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function get1x2Pixel(string $colorTop, ?string $colorBottom = null, int $repeat = 1): string
    {
        if ($colorBottom === null) {
            $colorBottom = $colorTop;
        }

        $colorTop = $this->translateColor($colorTop);
        $colorBottom = $this->translateColor($colorBottom);

        switch (true) {
            case $colorTop === self::NAME_TRANSPARENT && $colorBottom === self::NAME_TRANSPARENT:
                return str_repeat(' ', $repeat);

            case $colorTop === self::NAME_TRANSPARENT:
                $rgb = Color::convertHexToRgbArray($colorBottom);
                return sprintf("\x1b[38;2;%d;%d;%dm%s\x1b[0m", $rgb['r'], $rgb['g'], $rgb['b'], str_repeat('▄', $repeat));

            case $colorBottom === self::NAME_TRANSPARENT:
                $rgb = Color::convertHexToRgbArray($colorTop);
                return sprintf("\x1b[38;2;%d;%d;%dm%s\x1b[0m", $rgb['r'], $rgb['g'], $rgb['b'], str_repeat('▀', $repeat));

            default:
                $rgbTop = Color::convertHexToRgbArray($colorTop);
                $rgbBottom = Color::convertHexToRgbArray($colorBottom);
                return sprintf(
                    "\x1b[38;2;%d;%d;%dm\x1b[48;2;%d;%d;%dm%s\x1b[0m",
                    $rgbTop['r'],
                    $rgbTop['g'],
                    $rgbTop['b'],
                    $rgbBottom['r'],
                    $rgbBottom['g'],
                    $rgbBottom['b'],
                    str_repeat('▀', $repeat)
                );
        }
    }
}
