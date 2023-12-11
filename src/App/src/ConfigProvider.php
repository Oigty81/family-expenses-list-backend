<?php

declare(strict_types=1);

namespace App;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            "config" => $this->getConfiguration(),
            //'templates'    => $this->getTemplates(),
        ];
    }
    
    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
       
        
        $aggregator = new ConfigAggregator([
            new PhpFileProvider(realpath(__DIR__) . '/../config/services_manager.config.php')
        ]);

        return $aggregator->getMergedConfig();
    }

    /**
     * Returns the Module configuration
     */

    public function getConfiguration(): array
    {
        $aggregator = new ConfigAggregator([
            new PhpFileProvider(realpath(__DIR__) . '/../config/{{,*.}global,{,*.}local}.php'),
        ]);

        return $aggregator->getMergedConfig();
    }

}
