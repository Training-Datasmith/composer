<?php

declare(strict_types=1);
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Composer;

use Symfony\Component\Console\Input\ArrayInput;

/**
 * Symfony console ArrayInput factory
 */
class ConsoleArrayInputFactory
{
    /**
     * Create arrayInput instance.
     */
    public function create(array $params): \Symfony\Component\Console\Input\ArrayInput
    {
        return new ArrayInput($params);
    }
}
