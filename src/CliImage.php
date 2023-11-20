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

namespace Ixnode\PhpCliImage;

use GdImage;
use Ixnode\PhpCliImage\Utils\Point;
use Ixnode\PhpContainer\File;
use Ixnode\PhpCliImage\Tests\Unit\CliImageTest;
use Ixnode\PhpCliImage\Utils\Color;
use Ixnode\PhpException\Case\CaseUnsupportedException;

/**
 * Class CliImage
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-08-13)
 * @since 0.1.0 (2023-08-13) First version.
 * @link CliImageTest
 */
class CliImage
{
    /* @see https://www.php.net/manual/de/function.imagesetinterpolation.php */
    protected const DEFAULT_IMAGE_MODE = IMG_BOX; // IMG_GAUSSIAN;

    protected const NAME_TRANSPARENT = 'transparent';

    /** @var array<string, Point> $points */
    protected array $points = [];

    protected GdImage $gdImage;

    /**
     * @param File|string $image
     * @param int $width
     * @throws CaseUnsupportedException
     */
    public function __construct(protected File|string $image, protected int $width = 80)
    {
        if (is_string($this->image)) {
            $this->gdImage = $this->resizeImageGd(
                $this->createGdImageFromGivenImageString($this->image),
                $width
            );

            return;
        }

        $this->gdImage = $this->resizeImageGd(
            $this->createGdImageFromGivenPath(
                $this->image->getPath()
            ),
            $width
        );
    }

    /**
     * Resize given GdImage.
     *
     * @param GdImage $gdImage
     * @param int $width
     * @return GdImage
     * @throws CaseUnsupportedException
     */
    protected function resizeImageGd(GdImage $gdImage, int $width): GdImage
    {
        $gdImageResized = imagescale($gdImage, $width, -1, self::DEFAULT_IMAGE_MODE);

        if ($gdImageResized === false) {
            throw new CaseUnsupportedException('Unable to resize given image.');
        }

        return $gdImageResized;
    }

    /**
     * Creates image from the given path.
     *
     * @param string $path
     * @return GdImage
     * @throws CaseUnsupportedException
     */
    protected function createGdImageFromGivenPath(string $path): GdImage
    {
        $imageInfo = $this->getImageInfo($path);

        /* Create image. */
        $gdImage = match ($imageInfo[2]) {
            IMAGETYPE_GIF => imagecreatefromgif($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            default => throw new CaseUnsupportedException(sprintf('Unsupported image type %d - %s.', $imageInfo[2], $imageInfo['mime'])),
        };

        if ($gdImage === false) {
            throw new CaseUnsupportedException('Unable to load image.');
        }

        return $gdImage;
    }

    /**
     * Creates image from the given image string.
     *
     * @param string $imageString
     * @return GdImage
     * @throws CaseUnsupportedException
     */
    protected function createGdImageFromGivenImageString(string $imageString): GdImage
    {
        $gdImage = imagecreatefromstring($imageString);

        if (!$gdImage instanceof GdImage) {
            throw new CaseUnsupportedException('Unable to create image.');
        }

        return $gdImage;
    }

    /**
     * Gets image info.
     *
     * @param string $path
     * @return string[]|int[]
     * @throws CaseUnsupportedException
     */
    protected function getImageInfo(string $path): array
    {
        /* Get information about image. */
        $imageInfo = getimagesize($path);

        if ($imageInfo === false) {
            throw new CaseUnsupportedException(sprintf('Unable to get image information from "%s".', $path));
        }

        return $imageInfo;
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
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function convertImageToLines(): array
    {
        $width = imagesx($this->gdImage);
        $height = imagesy($this->gdImage);

        $points = $this->getPoints();

        $lines = [];
        for ($lineY = 0; $lineY < floor($height / 2); $lineY++) {
            $line = '';
            for ($cellX = 0; $cellX < $width; $cellX++) {
                $lineYTop = 2 * $lineY;
                $lineYBottom = 2 * $lineY + 1;

                $colorTop = imagecolorat($this->gdImage, $cellX, $lineYTop);
                $colorBottom = $lineYBottom + 1 <= $height ? imagecolorat($this->gdImage, $cellX, $lineYBottom) : null;

                if ($colorTop === false) {
                    throw new CaseUnsupportedException('Unable to get pixel from image.');
                }
                if ($colorBottom === false) {
                    throw new CaseUnsupportedException('Unable to get pixel from image.');
                }

                $line .= $this->get1x2Pixel(
                    $this->getColor($cellX, $lineYTop, Color::convertIntToHex($colorTop), $points),
                    $this->getColor($cellX, $lineYBottom, $colorBottom === null ? self::NAME_TRANSPARENT : Color::convertIntToHex($colorBottom), $points),
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

    /**
     * Returns the ascii representation of the image (string array).
     *
     * @return array<int, string>
     * @throws CaseUnsupportedException
     */
    public function getAsciiLines(): array
    {
        return $this->convertImageToLines();
    }

    /**
     * Returns the ascii representation of the image (string).
     *
     * @return string
     * @throws CaseUnsupportedException
     */
    public function getAsciiString(): string
    {
        return implode(PHP_EOL, $this->getAsciiLines());
    }

    /**
     * Returns the given coordinates.
     *
     * @return array<string, Point>
     */
    public function getPoints(): array
    {
        return $this->points;
    }

    /**
     * Sets the given coordinates.
     *
     * Format:
     * [
     *     'color1' => new Point(x, y),
     *     'color2' => new Point(x, y),
     * ]
     *
     * @param array<string, Point> $points
     * @return self
     */
    public function setPoints(array $points): self
    {
        $this->points = $points;
        return $this;
    }

    /**
     * Adds the given coordinate.
     *
     * @param string $color
     * @param Point $point
     * @return self
     */
    public function addCoordinate(string $color, Point $point): self
    {
        $this->points[$color] = $point;

        return $this;
    }

    /**
     * Adds the given spherical coordinate.
     *
     * @param string $color
     * @param float $latitude
     * @param float $longitude
     * @param string $typeProjection
     * @return self
     * @throws CaseUnsupportedException
     */
    public function addCoordinateSpherical(
        string $color,
        float $latitude,
        float $longitude,
        string $typeProjection = Point::TYPE_PROJECTION_KAVRAYSKIY_VII
    ): self
    {
        $this->points[$color] = new Point(
            $latitude,
            $longitude,
            Point::TYPE_COORDINATE_SYSTEM_SPHERICAL,
            $typeProjection,
            $this->getGdImageWidth(),
            $this->getGdImageHeight()
        );

        return $this;
    }

    /**
     * @return GdImage
     */
    public function getGdImage(): GdImage
    {
        return $this->gdImage;
    }

    /**
     * Returns the width of the GdImage object.
     *
     * @return int
     */
    public function getGdImageWidth(): int
    {
        return imagesx($this->gdImage);
    }

    /**
     * Returns the height of the GdImage object.
     *
     * @return int
     */
    public function getGdImageHeight(): int
    {
        return imagesy($this->gdImage);
    }
}
