<?php

declare(strict_types=1);

namespace My\Custom;

use OpenEuropa\TaskRunner\Traits\ConfigurationTokensTrait;

/**
 * Allows access to ConfigurationTokensTrait trait.
 *
 * @see \OpenEuropa\TaskRunner\Tests\ConfigurationTokensTest
 */
class TestConfigurationTokensTraitWrapper
{
    use ConfigurationTokensTrait;
}
