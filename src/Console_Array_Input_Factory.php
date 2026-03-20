<?php

declare (strict_types=1);
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Composer;

use Symfony\Component\Console\Input\Array_Input;
/**
 * Symfony console ArrayInput factory
 */
class Console_Array_Input_Factory
{
    /**
     * Create arrayInput instance.
     */
    public function create(array $params): \Symfony\Component\Console\Input\Array_Input
    {
        return new Array_Input($params);
    }
}