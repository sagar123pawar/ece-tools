<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MagentoCloud\Config\Database;

use Magento\MagentoCloud\Config\ConfigMerger;
use Magento\MagentoCloud\Config\Stage\DeployInterface;

class ResourceConfig implements ConfigInterface
{
    const KEY_RESOURCE = 'resource';
    const KEY_CONNECTION = 'connection';

    /**
     * Names fo resources
     */
    const RESOURCE_CHECKOUT = 'checkout';
    const RESOURCE_SALE = 'sale';
    const RESOURCE_DEFAULT_SETUP = 'default_setup';

    /**
     * Resource list
     */
    const RESOURCE_LIST = [
        self::RESOURCE_CHECKOUT,
        self::RESOURCE_SALE,
        self::RESOURCE_DEFAULT_SETUP,
    ];

    /**
     * Resources map
     */
    const RESOURCE_MAP = [
        DbConfig::CONNECTION_DEFAULT => self::RESOURCE_DEFAULT_SETUP,
        DbConfig::CONNECTION_CHECKOUT => self::RESOURCE_CHECKOUT,
        DbConfig::CONNECTION_SALE => self::RESOURCE_SALE,
    ];

    /**
     * Returns merged final database configuration.
     *
     * @var DbConfig
     */
    private $dbConfig;

    /**
     * Class for configuration merging
     *
     * @var ConfigMerger
     */
    private $configMerger;

    /**
     * Final configuration for deploy phase
     *
     * @var DeployInterface
     */
    private $stageConfig;

    /**
     * Final database configuration after merging
     *
     * @var array
     */
    private $mergedConfig;

    /**
     * @param DbConfig $dbConfig
     * @param ConfigMerger $configMerger
     * @param DeployInterface $stageConfig
     */
    public function __construct(
        DbConfig $dbConfig,
        ConfigMerger $configMerger,
        DeployInterface $stageConfig
    )
    {
        $this->dbConfig = $dbConfig;
        $this->stageConfig = $stageConfig;
        $this->configMerger = $configMerger;
    }

    /**
     * Returns resource configuration
     *
     * @return array
     */
    public function get(): array
    {
        if (null !== $this->mergedConfig) {
            return $this->mergedConfig;
        }

        $envConfig = $this->stageConfig->get(DeployInterface::VAR_RESOURCE_CONFIGURATION);

        foreach (self::RESOURCE_MAP as $connectionName => $resourceName) {
            if (in_array($connectionName, DbConfig::SPLIT_CONNECTIONS)
                && isset($envConfig[$resourceName])) {
                unset($envConfig[$resourceName]);
            }
        }

        if (!$this->configMerger->isEmpty($envConfig) && !$this->configMerger->isMergeRequired($envConfig)) {
            return $this->configMerger->clear($envConfig);
        }

        $connections = array_keys($this->dbConfig->get()[DbConfig::KEY_CONNECTION] ?? []);

        $config = [];

        foreach ($connections as $connectionName) {
            if (isset(self::RESOURCE_MAP[$connectionName])) {
                $config[self::RESOURCE_MAP[$connectionName]][self::KEY_CONNECTION] = $connectionName;
            }
        }

        return $this->mergedConfig = $this->configMerger->merge($config, $envConfig);
    }
}