<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Commands;

use Gitonomy\Git\Reference\Branch;
use OpenEuropa\TaskRunner\Contract\ComposerAwareInterface as ComposerAware;
use OpenEuropa\TaskRunner\Contract\RepositoryAwareInterface as RepositoryAware;
use OpenEuropa\TaskRunner\Contract\TimeAwareInterface as TimeAware;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits\ComposerAwareTrait;
use OpenEuropa\TaskRunner\Traits\RepositoryAwareTrait;
use OpenEuropa\TaskRunner\Traits\TimeAwareTrait;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;

/**
 * Project release commands.
 */
class ReleaseCommands extends AbstractCommands implements ComposerAware, RepositoryAware, TimeAware
{
    use ComposerAwareTrait;
    use RepositoryAwareTrait;
    use TimeAwareTrait;
    use TaskRunnerTasks\CollectionFactory\loadTasks;

    /**
     * Set runtime configuration values.
     *
     * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
     *
     * @hook command-event release:create-archive
     */
    public function setRuntimeConfig(ConsoleCommandEvent $event)
    {
        $timeFormat = $this->getConfig()->get('release.time_format');
        $dateFormat = $this->getConfig()->get('release.date_format');
        $timestamp = $this->getTime()->getTimestamp();

        $this->getConfig()->set('release.version', $this->getVersionString());
        $this->getConfig()->set('release.date', date($dateFormat, $timestamp));
        $this->getConfig()->set('release.time', date($timeFormat, $timestamp));
        $this->getConfig()->set('release.timestamp', $timestamp);
    }

    /**
     * Create a release for the current project.
     *
     * This command creates a .tag.gz archive for the current project named as
     * follow:
     *
     * [PROJECT-NAME]-[CURRENT-TAG].tar.gz
     *
     * If the current commit is not tagged then the current local branch name will
     * be used:
     *
     * [PROJECT-NAME]-[BRANCH-NAME].tar.gz
     *
     * When running the release command will create a temporary release directory
     * named after the project itself. Such a directory will be deleted after
     * the project archive is created.
     *
     * If you wish to keep the directory use the "--keep" option.
     *
     * If you wish to override the current tag use the "--tag" option.
     *
     * Before the release directory is archived you can run a list of packaging
     * commands in your runner.yml.dist, as shown below:
     *
     * > release:
     * >   tasks:
     * >     - { task: "copy", from: "css",    to: "my-project/css" }
     * >     - { task: "copy", from: "fonts",  to: "my-project/fonts" }
     * >     - { task: "copy", from: "images", to: "my-project/images" }
     *
     * @param array $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command release:create-archive
     *
     * @option tag  Release tag, will override current repository tag.
     * @option keep Whereas to keep the temporary release directory or not.
     *
     * @aliases release:ca,rca
     */
    public function createRelease(array $options = [
        'tag' => InputOption::VALUE_OPTIONAL,
        'keep' => false,
    ])
    {
        $name = $this->composer->getProject();
        $version = $options['tag'] !== null ? $options['tag'] : $this->getVersionString();
        $archive = "$name-$version.tar.gz";

        $tasks = [
            // Make sure we do not have a release directory yet.
            $this->taskFilesystemStack()->remove([$archive, $name]),

            // Get non-modified code using git archive.
            $this->taskGitStack()->exec(["archive", "HEAD", "-o $name.zip"]),
            $this->taskExtract("$name.zip")->to("$name"),
            $this->taskFilesystemStack()->remove("$name.zip"),
        ];

        // Append release tasks defined in runner.yml.dist.
        $releaseTasks = $this->getConfig()->get("release.tasks");
        $tasks[] = $this->taskCollectionFactory($releaseTasks);

        // Create archive.
        $tasks[] = $this->taskExecStack()->exec("tar -czf $archive $name");

        // Remove release directory, if not specified otherwise.
        if (!$options['keep']) {
            $tasks[] = $this->taskFilesystemStack()->remove($name);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Return version string for current HEAD: either a tag or local branch name.
     *
     * @return string
     *   Tag name or empty string if none set.
     */
    private function getVersionString()
    {
        $repository = $this->getRepository();
        $revision = $repository->getHead()->getRevision();

        // Get current commit hash.
        $hash = $repository->getHead()->getCommit()->getHash();

        // Resolve tags for current HEAD.
        // In case of multiple tags per commit take the latest one.
        $tags = $repository->getReferences()->resolveTags($hash);
        $tag = end($tags);

        // Resolve local branch name for current HEAD.
        $filter = function (Branch $branch) use ($revision) {
            return $branch->isLocal() && $branch->getRevision() === $revision;
        };
        $branches = array_filter($repository->getReferences()->getBranches(), $filter);
        $branch = reset($branches);

        // Make sure we always have a version string, i.e. when in detached state.
        $version = $hash;

        // If HEAD is attached use branch name.
        if ($branch !== false) {
            $version = $branch->getName();
        }

        // Current commit is tagged, prefer tag.
        if ($tag !== false) {
            $version = $tag->getName();
        }

        return $version;
    }
}
