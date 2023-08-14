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

use Ixnode\PhpException\Case\CaseUnsupportedException;

/**
 * Class Point
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-08-14)
 * @since 0.1.0 (2023-08-14) First version.
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Point
{
    protected float $x;

    protected float $y;

    final public const TYPE_COORDINATE_SYSTEM_CARTESIAN = 'cartesian';

    final public const TYPE_COORDINATE_SYSTEM_SPHERICAL = 'spherical';

    final public const TYPE_PROJECTION_NONE = 'none';

    final public const TYPE_PROJECTION_KAVRAYSKIY_VII = 'kavrayskiy-vii';

    /**
     * @param float $x
     * @param float $y
     * @param string $typeCoordinateSystem
     * @param string $typeProjection
     * @param int|null $width
     * @param int|null $height
     * @throws CaseUnsupportedException
     */
    public function __construct(
        float $x,
        float $y,
        string $typeCoordinateSystem = self::TYPE_COORDINATE_SYSTEM_CARTESIAN,
        string $typeProjection = self::TYPE_PROJECTION_NONE,
        int|null $width = null,
        int|null $height = null
    )
    {
        switch ($typeCoordinateSystem) {
            /* Cartesian coordinate system. */
            case self::TYPE_COORDINATE_SYSTEM_CARTESIAN:
                $this->x = $x;
                $this->y = $y;
                break;

            /* Spherical coordinate system. */
            case self::TYPE_COORDINATE_SYSTEM_SPHERICAL:
                if (is_null($width) || is_null($height)) {
                    throw new CaseUnsupportedException('Spherical coordinates requires width and height.');
                }

                $point = $this->getPointFromSpherical(
                    $x,
                    $y,
                    $width,
                    $height,
                    $typeProjection
                );
                $this->x = $point->getX();
                $this->y = $point->getY();
                break;

            default:
                throw new CaseUnsupportedException(sprintf('Invalid coordinate system "%s"', $typeCoordinateSystem));
        }
    }

    /**
     * @return float
     */
    public function getX(): float
    {
        return $this->x;
    }

    /**
     * @param float $x
     * @return self
     */
    public function setX(float $x): self
    {
        $this->x = $x;

        return $this;
    }

    /**
     * @return float
     */
    public function getY(): float
    {
        return $this->y;
    }

    /**
     * @param float $y
     * @return self
     */
    public function setY(float $y): self
    {
        $this->y = $y;

        return $this;
    }

    /**
     * Returns the converted spherical point on map.
     *
     * - see https://en.wikipedia.org/wiki/Kavrayskiy_VII_projection
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $width
     * @param int $height
     * @param string $typeProjection
     * @return Point
     * @throws CaseUnsupportedException
     */
    private function getPointFromSpherical(
        float $latitude,
        float $longitude,
        int $width,
        int $height,
        string $typeProjection
    ): Point
    {
        return match ($typeProjection) {
            self::TYPE_PROJECTION_KAVRAYSKIY_VII => $this->getPointKavrayskiyViiProjection(
                $latitude,
                $longitude,
                $width,
                $height
            ),
            default => throw new CaseUnsupportedException(sprintf('Invalid projection "%s" given.', $typeProjection)),
        };
    }

    /**
     * Returns the converted point on map (via Kavrayskiy VII projection).
     *
     * - see https://en.wikipedia.org/wiki/Kavrayskiy_VII_projection
     *
     * @param int $width
     * @param int $height
     * @param float $latitude
     * @param float $longitude
     * @return Point
     * @throws CaseUnsupportedException
     */
    private function getPointKavrayskiyViiProjection(
        float $latitude,
        float $longitude,
        int $width,
        int $height
    ): Point
    {
        /* Map scale */
        $widthMap = $width * 1.42;
        $heightMap = $height * 1.25;

        /* Map center */
        $xMove = -1 * $widthMap * .17;
        $yMove = -1 * $heightMap * .01;

        $widthDegree = $widthMap / 360;
        $heightDegree = $heightMap / 180;

        $pointXMiddle = $widthMap / 2 + $xMove;
        $pointYMiddle = $heightMap / 2 + $yMove;

        $latitudeRadian = deg2rad($latitude);
        $longitudeRadian = deg2rad($longitude);

        /* https://en.wikipedia.org/wiki/Kavrayskiy_VII_projection */
        $longitude = rad2deg(3 * $longitudeRadian / 2 * sqrt(1/3 - ($latitudeRadian / pi()) ** 2));

        return new Point(
            (int) round($pointXMiddle + $longitude * $widthDegree),
            (int) round($pointYMiddle - $latitude * $heightDegree),
        );
    }
}
