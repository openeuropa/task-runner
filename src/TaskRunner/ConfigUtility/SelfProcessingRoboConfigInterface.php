<?php

namespace OpenEuropa\TaskRunner\TaskRunner\ConfigUtility;

use Consolidation\Config\ConfigInterface;
use Consolidation\Config\Util\ConfigInterpolatorInterface;
use Consolidation\Config\Util\ConfigRuntimeInterface;

interface SelfProcessingRoboConfigInterface extends DependencyUpdaterInterface, ConfigInterface, ConfigInterpolatorInterface, ConfigRuntimeInterface
{

    /**
     * @return \Consolidation\Config\ConfigInterface|\Consolidation\Config\Util\ConfigInterpolatorInterface|\Consolidation\Config\Util\ConfigRuntimeInterface
     */
    public function getUnprocessedConfig();

}
