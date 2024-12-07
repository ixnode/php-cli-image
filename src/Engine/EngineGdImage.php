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

namespace Ixnode\PhpCliImage\Engine;

use GdImage;
use Ixnode\PhpCliImage\CliImage;
use Ixnode\PhpCliImage\Engine\Base\BaseEngine;
use Ixnode\PhpCliImage\Utils\Color;
use Ixnode\PhpContainer\File;
use Ixnode\PhpException\Case\CaseUnsupportedException;

/**
 * Class EngineGdImage
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-08-13)
 * @since 0.1.0 (2023-08-13) First version.
 */
class EngineGdImage extends BaseEngine
{
    /* @see https://www.php.net/manual/de/function.imagesetinterpolation.php */
    protected const DEFAULT_IMAGE_MODE = IMG_BOX; // IMG_GAUSSIAN;

    protected GdImage $gdImage;

    /**
     * @param CliImage $cliImage
     * @param File|string $image
     * @param int $width
     * @throws CaseUnsupportedException
     */
    public function __construct(CliImage $cliImage, File|string $image, int $width = 80)
    {
        parent::__construct($cliImage, $image, $width);

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
     * Returns the ascii representation of the image (string array).
     *
     * @inheritdoc
     * @throws CaseUnsupportedException
     */
    public function getAsciiLines(): array
    {
        return $this->convertImageToLines();
    }

    /**
     * Returns the ascii representation of the image (string).
     *
     * @inheritdoc
     * @throws CaseUnsupportedException
     */
    public function getAsciiString(): string
    {
        return implode(PHP_EOL, $this->getAsciiLines());
    }

    /**
     * Returns the width of the GdImage object.
     *
     * @inheritdoc
     */
    public function getWidth(): int
    {
        return imagesx($this->gdImage);
    }

    /**
     * Returns the height of the GdImage object.
     *
     * @inheritdoc
     */
    public function getHeight(): int
    {
        return imagesy($this->gdImage);
    }

    /**
     * Returns the color code at position.
     *
     * @inheritdoc
     * @throws CaseUnsupportedException
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getColorAt(int $posX, int $posY): string
    {
        $color = imagecolorat($this->gdImage, $posX, $posY);

        if ($color === false) {
            throw new CaseUnsupportedException('Unable to get pixel from image.');
        }

        return Color::convertIntToHex($color);
    }
}
