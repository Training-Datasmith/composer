<?php

declare (strict_types=1);
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Composer;

/**
 * Class RequireUpdateDryRunCommand calls composer require and update --dry-run commands
 */
class Require_Update_Dry_Run_Command
{
    protected \Magento\Composer\Magento_Composer_Application $magento_composer_application;
    protected \Magento\Composer\Info_Command $info_command;
    /**
     * Constructor
     */
    public function __construct(Magento_Composer_Application $magento_composer_application, Info_Command $info_command)
    {
        $this->magento_composer_application = $magento_composer_application;
        $this->info_command = $info_command;
    }
    /**
     * Runs composer update --dry-run command
     *
     * @param array $packages
     * @param string|null $workingDir
     * @return string
     * @throws \RuntimeException
     */
    public function run($packages, $working_dir = null)
    {
        try {
            // run require
            $this->magento_composer_application->run_composer_command(['command' => 'require', 'packages' => $packages, '--no-update' => true], $working_dir);
            $output = $this->magento_composer_application->run_composer_command(['command' => 'update', '--dry-run' => true], $working_dir);
        } catch (\RuntimeException $e) {
            $error_message = $this->generate_additional_error_message($e->get_message(), $packages);
            if ($error_message) {
                throw new \RuntimeException($error_message, $e->get_code(), $e);
            }
            throw new \RuntimeException($e->get_message(), $e->get_code(), $e);
        }
        return $output;
    }
    /**
     * Generates additional explanation for error message
     *
     * @param string $message
     * @param array $inputPackages
     * @return string
     */
    protected function generate_additional_error_message($message, $input_packages)
    {
        $matches = [];
        $error_message = '';
        $packages = [];
        $raw_lines = explode(PHP_EOL, $message);
        foreach ($raw_lines as $line) {
            if (preg_match('/- (.*) requires (.*) -> no matching package/', $line, $matches)) {
                $packages[] = $matches[1];
                $packages[] = $matches[2];
            }
        }
        if (!empty($packages)) {
            $packages = array_unique($packages);
            $packages = $this->explode_packages_and_versions($packages);
            $input_packages = $this->explode_packages_and_versions($input_packages);
            $update = [];
            $conflicts = [];
            foreach ($input_packages as $package => $version) {
                if (isset($packages[$package])) {
                    $update[] = $package . ' to ' . $version;
                }
            }
            foreach (array_diff_key($packages, $input_packages) as $package => $version) {
                if (!$package_info = $this->info_command->run($package, true)) {
                    return false;
                }
                $current_version = $package_info[Info_Command::CURRENT_VERSION];
                if (empty($package_info[Info_Command::AVAILABLE_VERSIONS])) {
                    $package_info = $this->info_command->run($package);
                    if (empty($package_info[Info_Command::AVAILABLE_VERSIONS])) {
                        return false;
                    }
                }
                $conflicts[] = ' - ' . $package . ' version ' . $current_version . '. ' . 'Please try to update it to one of the following package versions: ' . implode(', ', $package_info['available_versions']);
            }
            $error_message = 'You are trying to update package(s) ' . implode(', ', $update) . PHP_EOL . "We've detected conflicts with the following packages:" . PHP_EOL . implode(PHP_EOL, $conflicts) . PHP_EOL;
        }
        return $error_message;
    }
    /**
     * Returns array that contains package as key and version as value
     *
     * @param array $packages
     */
    protected function explode_packages_and_versions($packages): array
    {
        $packages_and_versions = [];
        foreach ($packages as $package) {
            $package = explode(' ', $package);
            $packages_and_versions[$package[0]] = $package[1];
        }
        return $packages_and_versions;
    }
}