<?php

namespace OpenEuropa\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use OpenEuropa\TaskRunner\Contract\RepositoryAwareInterface;
use OpenEuropa\TaskRunner\Traits\ComposerAwareTrait;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits\RepositoryAwareTrait;

/**
 * Project release commands.
 */
class ReleaseCommands extends AbstractCommands implements ComposerAwareInterface, RepositoryAwareInterface
{
    use ComposerAwareTrait;
    use RepositoryAwareTrait;
    use TaskRunnerTasks\CollectionFactory\loadTasks;

  /**
   * Create project release archive.
   *
   * The command will create an archive file containing the release package.
   *
   * @param array $options
   *   Command options.
   *
   * @return \Robo\Collection\CollectionBuilder
   *   Collection builder.
   *
   * @command release:create-archive
   *
   * @option keep Whereas to keep the temporary release directory or not.
   *
   * @aliases release:ca,rca
   */
    public function createRelease(array $options = ['keep' => false])
    {
        if ($this->getRepository()->isHeadDetached()) {
            throw new \RuntimeException('Release cannot be generated in detached state.');
        }

        $name = $this->composer->getProject();
        $version = $this->getVersionString();
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

        // Get commit has from current HEAD.
        $hash = $repository->getHead()->getCommitHash();

        // Resolve tags for current HEAD.
        // In case of multiple tags per commit take the latest one.
        $tags = $repository->getReferences()->resolveTags($hash);
        $tag = end($tags);

        // Resolve local branch name for current HEAD.
        $branches = array_filter($repository->getReferences()->resolveBranches($hash), function ($branch) {
            return $branch->isLocal();
        });
        $branch = reset($branches);

        return ($tag !== false) ? $tag->getName() : $branch->getName();
    }
}
