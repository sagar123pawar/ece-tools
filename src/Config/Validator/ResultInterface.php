<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MagentoCloud\Config\Validator;

/**
 * Validation result.
 */
interface ResultInterface
{
    /**
     * Type for error result
     */
    const ERROR = 'error';

    /**
     * Type for success result
     */
    const SUCCESS = 'success';
}
