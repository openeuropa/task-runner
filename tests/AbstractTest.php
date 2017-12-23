<?php

namespace EC\OpenEuropa\TaskRunner\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AbstractTest
 *
 * @package EC\OpenEuropa\TaskRunner\Tests
 */
abstract class AbstractTest extends TestCase
{

  /**
   * Call protected/private method of a class.
   *
   * @param object &$object    Instantiated object that we will run method on.
   * @param string $methodName Method name to call
   * @param array  $parameters Array of parameters to pass into method.
   *
   * @return mixed Method return.
   */
    protected function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @param $filepath
     *
     * @return mixed
     */
    protected function getFixtureContent($filepath)
    {
        return Yaml::parse(file_get_contents(__DIR__."/fixtures/{$filepath}"));
    }

    /**
     * @param $name
     *
     * @return string
     */
    protected function getSandboxPath($name)
    {
        return __DIR__."/sandbox/{$name}";
    }
}
