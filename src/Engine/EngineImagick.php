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

use Imagick;
use ImagickException;
use ImagickPixelException;
use Ixnode\PhpCliImage\CliImage;
use Ixnode\PhpCliImage\Engine\Base\BaseEngine;
use Ixnode\PhpCliImage\Utils\Color;
use Ixnode\PhpContainer\File;
use Ixnode\PhpException\Case\CaseUnsupportedException;

/**
 * Class EngineImagick
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2024-12-07)
 * @since 0.1.0 (2024-12-07) Add Imagick engine.
 */
class EngineImagick extends BaseEngine
{
    /* @see https://www.php.net/manual/en/imagick.constants.php#imagick.constants.filters */
    protected const DEFAULT_IMAGE_MODE = Imagick::FILTER_BOX; // Imagick::FILTER_GAUSSIAN;

    protected Imagick $imagick;

    /**
     * @param CliImage $cliImage
     * @param File|string $image
     * @param int $width
     * @throws CaseUnsupportedException
     * @throws ImagickException
     */
    public function __construct(CliImage $cliImage, File|string $image, int $width = 80)
    {
        parent::__construct($cliImage, $image, $width);

        if (is_string($this->image)) {
            $this->imagick = $this->resizeImage(
                $this->createImageFromGivenImageString($this->image),
                $width
            );

            return;
        }

        $this->imagick = $this->resizeImage(
            $this->createImageFromGivenPath(
                $this->image->getPath()
            ),
            $width
        );
    }

    /**
     * Resize given image.
     *
     * @param Imagick $imagick
     * @param int $width
     * @return Imagick
     * @throws CaseUnsupportedException
     * @throws ImagickException
     */
    protected function resizeImage(Imagick $imagick, int $width): Imagick
    {
        $originalWidth = $imagick->getImageWidth();
        $originalHeight = $imagick->getImageHeight();

        $aspectRatio = $originalWidth / $originalHeight;

        $height = (int) ($width / $aspectRatio);

        $success = $imagick->resizeImage($width, $height, self::DEFAULT_IMAGE_MODE, 1);

        if (!$success) {
            throw new CaseUnsupportedException('Unable to resize given image.');
        }

        return $imagick;
    }

    /**
     * Creates image from the given path.
     *
     * @param string $path
     * @return Imagick
     * @throws CaseUnsupportedException
     * @throws ImagickException
     */
    protected function createImageFromGivenPath(string $path): Imagick
    {
        $imagick = new Imagick();
        $success = $imagick->readImage($path);

        if (!$success) {
            throw new CaseUnsupportedException('Unable to load image.');
        }

        return $imagick;
    }

    /**
     * Creates image from the given image string.
     *
     * @param string $imageString
     * @return Imagick
     * @throws CaseUnsupportedException
     * @throws ImagickException
     */
    protected function createImageFromGivenImageString(string $imageString): Imagick
    {
        $imagick = new Imagick();
        $success = $imagick->readImageBlob($imageString);

        if (!$success) {
            throw new CaseUnsupportedException('Unable to create image.');
        }

        return $imagick;
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
     * @throws ImagickException
     */
    public function getWidth(): int
    {
        return $this->imagick->getImageWidth();
    }

    /**
     * Returns the height of the GdImage object.
     *
     * @inheritdoc
     * @throws ImagickException
     */
    public function getHeight(): int
    {
        return $this->imagick->getImageHeight();
    }

    /**
     * Returns the color code at position.
     *
     * @inheritdoc
     * @throws ImagickException
     * @throws ImagickPixelException
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getColorAt(int $posX, int $posY): string
    {
        $color = $this->imagick->getImagePixelColor($posX, $posY);

        return Color::convertImagickPixelToHex($color);
    }
}
