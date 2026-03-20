<?php

declare (strict_types=1);
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Composer;

use Composer\Console\Application;
use Composer\Factory as ComposerFactory;
use Composer\IO\Buffer_Io;
use Symfony\Component\Console\Output\Buffered_Output;
/**
 * Class MagentoComposerApplication
 *
 * This class provides ability to set composer application settings and run any composer command.
 * Also provides method to get Composer instance so you can have access composer properties lie Locker
 */
class Magento_Composer_Application
{
    public const COMPOSER_WORKING_DIR = '--working-dir';
    /**
     * Path to composer.json file
     *
     * @var string
     */
    private $composer_json;
    /**
     * Buffered output
     */
    private \Symfony\Component\Console\Output\Buffered_Output $console_output;
    private \Magento\Composer\Console_Array_Input_Factory $console_array_input_factory;
    /**
     * @var Application
     */
    private $console_application;
    /**
     * Constructs class
     *
     * @param string $pathToComposerJson
     * @param Application $consoleApplication
     * @param ConsoleArrayInputFactory $consoleArrayInputFactory
     * @param BufferedOutput $consoleOutput
     */
    public function __construct(string $path_to_composer_home, $path_to_composer_json, ?Application $console_application = null, ?Console_Array_Input_Factory $console_array_input_factory = null, ?Buffered_Output $console_output = null)
    {
        $this->console_application = $console_application ?: new Application();
        $this->console_array_input_factory = $console_array_input_factory ?: new Console_Array_Input_Factory();
        $this->console_output = $console_output ?: new Buffered_Output();
        $this->composer_json = $path_to_composer_json;
        putenv('COMPOSER_HOME=' . $path_to_composer_home);
        $this->console_application->set_auto_exit(false);
    }
    /**
     * Creates composer object
     *
     * @return \Composer\Composer
     * @throws \Exception
     */
    public function create_composer()
    {
        return Composer_Factory::create(new Buffer_Io(), $this->composer_json);
    }
    /**
     * Runs composer command
     *
     * @param string|null $workingDir
     * @throws \RuntimeException
     */
    public function run_composer_command(array $command_params, $working_dir = null): string
    {
        $this->console_application->reset_composer();
        if ($working_dir) {
            $command_params[self::COMPOSER_WORKING_DIR] = $working_dir;
        } else {
            $command_params[self::COMPOSER_WORKING_DIR] = dirname($this->composer_json);
        }
        $input = $this->console_array_input_factory->create($command_params);
        $exit_code = $this->console_application->run($input, $this->console_output);
        if ($exit_code) {
            throw new \RuntimeException(sprintf('Command "%s" failed: %s', $command_params['command'], $this->console_output->fetch()));
        }
        return $this->console_output->fetch();
    }
}