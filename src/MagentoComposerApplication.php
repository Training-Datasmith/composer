<?php

declare(strict_types=1);
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Composer;

use Composer\Console\Application;
use Composer\Factory as ComposerFactory;
use Composer\IO\BufferIO;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class MagentoComposerApplication
 *
 * This class provides ability to set composer application settings and run any composer command.
 * Also provides method to get Composer instance so you can have access composer properties lie Locker
 */
class MagentoComposerApplication
{
    public const COMPOSER_WORKING_DIR = '--working-dir';

    /**
     * Path to composer.json file
     *
     * @var string
     */
    private $composerJson;

    /**
     * Buffered output
     */
    private \Symfony\Component\Console\Output\BufferedOutput $consoleOutput;

    private \Magento\Composer\ConsoleArrayInputFactory $consoleArrayInputFactory;

    /**
     * @var Application
     */
    private $consoleApplication;

    /**
     * Constructs class
     *
     * @param string $pathToComposerJson
     * @param Application $consoleApplication
     * @param ConsoleArrayInputFactory $consoleArrayInputFactory
     * @param BufferedOutput $consoleOutput
     */
    public function __construct(
        string $pathToComposerHome,
        $pathToComposerJson,
        ?Application $consoleApplication = null,
        ?ConsoleArrayInputFactory $consoleArrayInputFactory = null,
        ?BufferedOutput $consoleOutput = null
    ) {
        $this->consoleApplication = $consoleApplication ?: new Application();
        $this->consoleArrayInputFactory = $consoleArrayInputFactory ?: new ConsoleArrayInputFactory();
        $this->consoleOutput = $consoleOutput ?: new BufferedOutput();

        $this->composerJson = $pathToComposerJson;

        putenv('COMPOSER_HOME=' . $pathToComposerHome);

        $this->consoleApplication->setAutoExit(false);
    }

    /**
     * Creates composer object
     *
     * @return \Composer\Composer
     * @throws \Exception
     */
    public function createComposer()
    {
        return ComposerFactory::create(new BufferIO(), $this->composerJson);
    }

    /**
     * Runs composer command
     *
     * @param string|null $workingDir
     * @throws \RuntimeException
     */
    public function runComposerCommand(array $commandParams, $workingDir = null): string
    {
        $this->consoleApplication->resetComposer();

        if ($workingDir) {
            $commandParams[self::COMPOSER_WORKING_DIR] = $workingDir;
        } else {
            $commandParams[self::COMPOSER_WORKING_DIR] = dirname($this->composerJson);
        }

        $input = $this->consoleArrayInputFactory->create($commandParams);

        $exitCode = $this->consoleApplication->run($input, $this->consoleOutput);

        if ($exitCode) {
            throw new \RuntimeException(
                sprintf('Command "%s" failed: %s', $commandParams['command'], $this->consoleOutput->fetch())
            );
        }

        return $this->consoleOutput->fetch();
    }
}
