<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tests\Commands;

use Gitonomy\Git\Commit;
use Gitonomy\Git\Reference;
use Gitonomy\Git\Repository;
use OpenEuropa\TaskRunner\Services\Composer;
use OpenEuropa\TaskRunner\Services\Time;
use OpenEuropa\TaskRunner\TaskRunner;
use OpenEuropa\TaskRunner\Tests\AbstractTest;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests the `release:*` commands.
 */
class ReleaseCommandsTest extends AbstractTest
{
    /**
     * @param array  $config
     * @param string $options
     * @param array  $repository
     * @param array  $contains
     * @param array  $notContains
     *
     * @dataProvider releaseCreateArchiveDataProvider
     */
    public function testReleaseCommand(array $config, $options, array $repository, array $contains, array $notContains)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');

        file_put_contents($configFile, Yaml::dump($config));

        $input = new StringInput(
            "release:create-archive {$options} --simulate --working-dir=" . $this->getSandboxRoot()
        );
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());

        $runner->getContainer()->share('task_runner.composer', $this->getComposerMock('test_project'));
        $runner->getContainer()->share('repository', $this->getRepositoryMock($repository));
        $runner->run();

        $text = $output->fetch();
        foreach ($contains as $row) {
            $this->assertContains($row, $text);
        }
        foreach ($notContains as $row) {
            $this->assertNotContains($row, $text);
        }
    }

    /**
     * @param array $config
     * @param array $repository
     * @param int   $timestamp
     * @param array $expected
     *
     * @dataProvider releaseDynamicTokens
     */
    public function testDynamicTokens(array $config, array $repository, $timestamp, array $expected)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');

        file_put_contents($configFile, Yaml::dump($config));

        $input = new StringInput("release:create-archive --simulate --working-dir=" . $this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());

        $runner->getContainer()->share('task_runner.time', $this->getTimeMock($timestamp));
        $runner->getContainer()->share('task_runner.composer', $this->getComposerMock('test_project'));
        $runner->getContainer()->share('repository', $this->getRepositoryMock($repository));
        $runner->run();

        foreach ($expected as $name => $value) {
            $this->assertEquals($value, $runner->getConfig()->get($name));
        }
    }

    /**
     * @return array
     */
    public function releaseCreateArchiveDataProvider()
    {
        return $this->getFixtureContent('commands/release-create-archive.yml');
    }

    /**
     * @return array
     */
    public function releaseDynamicTokens()
    {
        return $this->getFixtureContent('commands/release-dynamic-tokens.yml');
    }

    /**
     * @param array $repository
     *
     * @return MockObject
     */
    protected function getRepositoryMock(array $repository)
    {
        $tags = [];
        if ($repository['tag']) {
            $mock = $this->createMock(Reference\Tag::class);
            $mock->method('getName')->willReturn($repository['tag']);
            $tags[] = $mock;
        }

        $branches = [];
        foreach ($repository['branches'] as $branch) {
            $mock = $this->createMock(Reference\Branch::class);
            $mock->method('getName')->willReturn($branch['name']);
            $mock->method('isLocal')->willReturn($branch['local']);
            $mock->method('getRevision')->willReturn($branch['revision']);
            $branches[] = $mock;
        }

        $mock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getHead',
                'getReferences',
                'resolveTags',
                'getBranches',
            ])
            ->getMock();

        $head = $this->getMockBuilder(Commit::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCommit',
                'getRevision',
                'getHash',
            ])
            ->getMock();
        $head->expects($this->any())->method('getCommit')->willReturnSelf();
        $head->expects($this->any())->method('getHash')->willReturn($repository['hash']);
        $head->expects($this->any())->method('getRevision')->willReturn($repository['revision']);

        $mock->expects($this->any())->method('getHead')->willReturn($head);
        $mock->expects($this->any())->method('getReferences')->willReturnSelf();
        $mock->expects($this->any())->method('resolveTags')->willReturn($tags);
        $mock->expects($this->any())->method('getBranches')->willReturn($branches);

        return $mock;
    }

    /**
     * @param string $name
     *
     * @return MockObject
     */
    protected function getComposerMock($name)
    {
        $mock = $this->createMock(Composer::class);
        $mock->method('getProject')->willReturn($name);

        return $mock;
    }

    /**
     * @param int $timestamp
     *
     * @return MockObject
     */
    protected function getTimeMock($timestamp)
    {
        $mock = $this->createMock(Time::class);
        $mock->method('getTimestamp')->willReturn($timestamp);

        return $mock;
    }
}
