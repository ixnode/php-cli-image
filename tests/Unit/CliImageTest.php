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

namespace Ixnode\PhpCliImage\Tests\Unit;

use Ixnode\PhpCliImage\CliImage;
use Ixnode\PhpContainer\File;
use Ixnode\PhpCoordinate\Coordinate;
use Ixnode\PhpException\Case\CaseUnsupportedException;
use Ixnode\PhpException\Parser\ParserException;
use PHPUnit\Framework\TestCase;

/**
 * Class CliImageTest
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-08-14)
 * @since 0.1.0 (2023-08-14) First version.
 * @link CliImage
 */
final class CliImageTest extends TestCase
{
    /**
     * Test wrapper.
     *
     * @dataProvider dataProviderSimple
     * @dataProvider dataProviderCoordinates
     *
     * @test
     * @testdox $number) Test CliImage: Method getAsciiString.
     * @param int $number
     * @param string $path
     * @param int $with
     * @param array<string, Coordinate>|null $coordinates
     * @param float|string $expected
     * @throws CaseUnsupportedException
     */
    public function wrapper(
        int          $number,
        string       $path,
        int          $with,
        array|null   $coordinates,
        float|string $expected
    ): void
    {
        /* Arrange */

        /* Act */
        $cliImage = new CliImage(new File($path));
        if (is_array($coordinates)) {
            $cliImage->setCoordinates($coordinates);
        }

        /* Assert */
        $this->assertIsNumeric($number); // To avoid phpmd warning.

        $result = $cliImage->getAsciiString($with);

        $this->assertSame($expected, $result);
    }

    /**
     * Data provider (simple).
     *
     * @return array<int, array<int, string|int|float|null|array<string, mixed>>>
     */
    public function dataProviderSimple(): array
    {
        $number = 0;

        return [

            /**
             * Check simple images.
             */
            [++$number, 'docs/image/world-map.png', 80, [], $this->getAsciiStringWorldMap('docs/text/world-map.txt')], // docs/image/world-map.png

        ];
    }

    /**
     * Data provider (coordinates).
     *
     * @return array<int, array<int, string|int|float|null|array<string, mixed>>>
     * @throws CaseUnsupportedException
     * @throws ParserException
     */
    public function dataProviderCoordinates(): array
    {
        $number = 0;

        return [

            /**
             * Check simple images.
             */
            [++$number, 'docs/image/world-map.png', 80, [
                '#ff0000' => new Coordinate(40.71, -74.01),
                '#00ff00' => new Coordinate(59.91, 10.75),
                '#0000ff' => new Coordinate(.0, .0),
            ], $this->getAsciiStringWorldMap('docs/text/world-map-coordinates.txt')],
        ];
    }

    /**
     * Returns the world map ascii string from given path.
     *
     * @param string $path
     * @return string
     * @throws CaseUnsupportedException
     */
    private function getAsciiStringWorldMap(string $path): string
    {
        $content = file_get_contents($path);

        if ($content === false) {
            throw new CaseUnsupportedException(sprintf('Unable to read file "%s".', $path));
        }

        return $content;
    }
}
