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

namespace Ixnode\PhpCliImage\Command;

use Ahc\Cli\Input\Command;
use Ahc\Cli\Output\Color;
use Ahc\Cli\Output\Writer;
use Exception;
use Ixnode\PhpContainer\File;
use Ixnode\PhpCliImage\CliImage;
use Ixnode\PhpCoordinate\Coordinate;

/**
 * Class CliImageCommand
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-08-13)
 * @since 0.1.0 (2023-08-13) First version.
 * @property string|null $pathInput
 * @property string|null $pathOutput
 */
class CliImageCommand extends Command
{
    private const SUCCESS = 0;

    private const INVALID = 2;

    private Writer $writer;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct('print:image', 'Prints given image path to cli as ascii string.');

        $this
            ->argument('path-input', 'The path of the image to display.')
            ->argument('path-output', 'The output path of the generated image.')
        ;
    }

    /**
     * Prints error message.
     *
     * @param string $message
     * @return void
     * @throws Exception
     */
    private function printError(string $message): void
    {
        $color = new Color();

        $this->writer->write(sprintf('%s%s', $color->error($message), PHP_EOL));
    }

    /**
     * Executes the CliImageCommand.
     *
     * @return int
     * @throws Exception
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function execute(): int
    {
        $this->writer = $this->writer();

        $pathInput = $this->pathInput;

        if (is_null($pathInput)) {
            $this->printError('No image path given.');
            return self::INVALID;
        }

        $file = new File($pathInput);

        if (!$file->exist()) {
            $this->printError(sprintf('Unable to find given file "%s".', $pathInput));
            return self::INVALID;
        }

        $image = new CliImage($file);
        $image->addCoordinate('#ff0000', new Coordinate(40.71, -74.01));
        $image->addCoordinate('#00ff00', new Coordinate(59.91, 10.75));
        $image->addCoordinate('#0000ff', new Coordinate(.0, .0));

        $width = 80;
        $this->writer->write($image->getAsciiString($width), true);

        if (!is_null($this->pathOutput)) {
            file_put_contents($this->pathOutput, $image->getAsciiString($width));
            $this->writer->write('---', true);
            $this->writer->write(sprintf('Image saved to "%s".', $this->pathOutput), true);
            $this->writer->write('---', true);
        }

        return self::SUCCESS;
    }
}
