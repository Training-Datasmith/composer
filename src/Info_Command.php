<?php

declare (strict_types=1);
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Composer;

/**
 * Class InfoCommand calls composer info command
 */
class Info_Command
{
    /**
     * Current version
     */
    public const CURRENT_VERSION = 'current_version';
    public const VERSIONS = 'versions';
    /**
     * Available versions
     */
    public const AVAILABLE_VERSIONS = 'available_versions';
    /**
     *  Package name
     */
    public const NAME = 'name';
    /**
     * New versions
     */
    public const NEW_VERSIONS = 'new_versions';
    protected \Magento\Composer\Magento_Composer_Application $magento_composer_application;
    /**
     * Constructor
     */
    public function __construct(Magento_Composer_Application $magento_composer_application)
    {
        $this->magento_composer_application = $magento_composer_application;
    }
    /**
     * Runs composer info command
     *
     * @param string $package
     * @param bool $installed
     * @return array|bool
     */
    public function run($package, $installed = false)
    {
        $show_all_packages = !$installed;
        $command_parameters = ['command' => 'info', 'package' => $package, '-i' => $installed, '--all' => $show_all_packages];
        try {
            $output = $this->magento_composer_application->run_composer_command($command_parameters);
        } catch (\RuntimeException $e) {
            return false;
        }
        $raw_lines = explode("\n", str_replace("\r\n", "\n", $output));
        $result = [];
        foreach ($raw_lines as $line) {
            $chunk = explode(':', $line);
            if (count($chunk) === 2) {
                $result[trim($chunk[0])] = trim($chunk[1]);
            }
        }
        $result = $this->extract_versions($result);
        if (!isset($result[self::NAME]) && isset($result[self::CURRENT_VERSION])) {
            $result[self::NAME] = $package;
        }
        return $result;
    }
    /**
     * Extracts package versions info
     */
    private function extract_versions(array $package_info): array
    {
        $versions = explode(', ', $package_info[self::VERSIONS]);
        $package_info[self::NEW_VERSIONS] = [];
        $package_info[self::AVAILABLE_VERSIONS] = [];
        if (count($versions) === 1) {
            $package_info[self::CURRENT_VERSION] = str_replace('* ', '', $package_info[self::VERSIONS]);
        } else {
            $current_version = array_values(preg_grep("/^\\*.*/", $versions));
            if ($current_version) {
                $package_info[self::CURRENT_VERSION] = str_replace('* ', '', $current_version[0]);
            } else {
                $package_info[self::CURRENT_VERSION] = '';
            }
            $package_info[self::AVAILABLE_VERSIONS] = array_values(preg_grep("/^\\*.*/", $versions, PREG_GREP_INVERT));
        }
        if (count($package_info[self::AVAILABLE_VERSIONS]) > 0) {
            if ($package_info[self::CURRENT_VERSION]) {
                foreach ($package_info[self::AVAILABLE_VERSIONS] as $version) {
                    if (version_compare($package_info[self::CURRENT_VERSION], $version, '<')) {
                        $package_info[self::NEW_VERSIONS][] = $version;
                    }
                }
            } else {
                $package_info[self::NEW_VERSIONS] = $package_info[self::AVAILABLE_VERSIONS];
            }
        }
        return $package_info;
    }
}