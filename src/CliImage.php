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

use ImagickException;
use Ixnode\PhpCliImage\Engine\Base\BaseEngine;
use Ixnode\PhpCliImage\Engine\EngineGdImage;
use Ixnode\PhpCliImage\Engine\EngineImagick;
use Ixnode\PhpCliImage\Utils\Point;
use Ixnode\PhpContainer\File;
use Ixnode\PhpCliImage\Tests\Unit\CliImageTest;
use Ixnode\PhpException\Case\CaseUnsupportedException;
use LogicException;

/**
 * Class CliImage
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.1 (2024-12-07)
 * @since 0.1.1 (2024-12-07) Add Imagick engine.
 * @since 0.1.0 (2023-08-13) First version.
 * @link CliImageTest
 */
class CliImage
{
    final public const ENGINE_GD_IMAGE = 'gd-image';

    final public const ENGINE_IMAGICK = 'imagick';

    private readonly BaseEngine $engine;

    /** @var array<string, Point> $points */
    protected array $points = [];

    /**
     * @param File|string $image
     * @param int $width
     * @param string $engineType
     * @throws CaseUnsupportedException
     * @throws ImagickException
     */
    public function __construct(protected File|string $image, protected int $width = 80, protected string $engineType  = self::ENGINE_GD_IMAGE)
    {
        $this->engine = match ($this->engineType) {
            self::ENGINE_GD_IMAGE => new EngineGdImage(cliImage: $this, image: $this->image, width: $this->width),
            self::ENGINE_IMAGICK => new EngineImagick(cliImage: $this, image: $this->image, width: $this->width),
            default => throw new LogicException(sprintf('Unsupported engine type "%s"', $this->engineType)),
        };
    }

    /**
     * Returns the ascii representation of the image (string array).
     *
     * @return array<int, string>
     * @throws CaseUnsupportedException
     */
    public function getAsciiLines(): array
    {
        return $this->engine->convertImageToLines();
    }

    /**
     * Returns the ascii representation of the image (string).
     *
     * @return string
     * @throws CaseUnsupportedException
     */
    public function getAsciiString(): string
    {
        return $this->engine->getAsciiString();
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
            $this->getWidth(),
            $this->getHeight()
        );

        return $this;
    }

    /**
     * Returns the width of this image.
     *
     * @return int
     */
    public function getWidth(): int
    {
        return $this->engine->getWidth();
    }

    /**
     * Returns the height of this image.
     *
     * @return int
     */
    public function getHeight(): int
    {
        return $this->engine->getHeight();
    }
}
