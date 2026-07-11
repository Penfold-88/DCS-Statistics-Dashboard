<?php

namespace DcsStats\Services\Admin;

final class GitStatusService
{
    private GitRepositoryLocator $repositoryLocator;
    private GitCommandRunner $commandRunner;

    public function __construct(?GitRepositoryLocator $repositoryLocator = null, ?GitCommandRunner $commandRunner = null)
    {
        $this->repositoryLocator = $repositoryLocator ?? new GitRepositoryLocator();
        $this->commandRunner = $commandRunner ?? new GitCommandRunner();
    }

    public function getStatus(): array
    {
        $response = [
            'success' => false,
            'branch' => 'unknown',
            'ahead' => 0,
            'behind' => 0,
            'modified' => 0,
            'untracked' => 0,
        ];

        $dashboardRoot = realpath(DCS_ROOT_PATH);
        if (!$dashboardRoot) {
            return $response;
        }

        $repoPath = $this->repositoryLocator->resolve($dashboardRoot);
        if ($repoPath === null) {
            return $response;
        }

        $gitErrors = [];
        $branch = $this->commandRunner->run($repoPath, ['rev-parse', '--abbrev-ref', 'HEAD'], $gitError);
        if ($gitError !== '') {
            $gitErrors[] = $this->commandRunner->cleanError($gitError, $repoPath);
        }

        if ($branch === '') {
            return $this->withGitErrors($response, $gitErrors);
        }

        $response['branch'] = $branch;
        $response['success'] = true;

        $upstream = $this->commandRunner->run($repoPath, ['rev-parse', '--abbrev-ref', '--symbolic-full-name', '@{u}'], $gitError);
        if ($gitError !== '') {
            $gitErrors[] = $this->commandRunner->cleanError($gitError, $repoPath);
        }

        if ($upstream !== '') {
            $counts = $this->commandRunner->run($repoPath, ['rev-list', '--left-right', '--count', 'HEAD...@{u}'], $gitError);
            if ($gitError !== '') {
                $gitErrors[] = $this->commandRunner->cleanError($gitError, $repoPath);
            }
            if ($counts !== '') {
                [$ahead, $behind] = preg_split('/\s+/', $counts);
                $response['ahead'] = (int)$ahead;
                $response['behind'] = (int)$behind;
            }
        }

        $modified = $this->commandRunner->run($repoPath, ['diff', '--name-only'], $gitError);
        if ($gitError !== '') {
            $gitErrors[] = $this->commandRunner->cleanError($gitError, $repoPath);
        }
        if ($modified !== '') {
            $response['modified'] = count(array_filter(explode("\n", $modified)));
        }

        $staged = $this->commandRunner->run($repoPath, ['diff', '--cached', '--name-only'], $gitError);
        if ($gitError !== '') {
            $gitErrors[] = $this->commandRunner->cleanError($gitError, $repoPath);
        }
        if ($staged !== '') {
            $response['staged'] = count(array_filter(explode("\n", $staged)));
        }

        $untracked = $this->commandRunner->run($repoPath, ['ls-files', '--others', '--exclude-standard'], $gitError);
        if ($gitError !== '') {
            $gitErrors[] = $this->commandRunner->cleanError($gitError, $repoPath);
        }
        if ($untracked !== '') {
            $response['untracked'] = count(array_filter(explode("\n", $untracked)));
        }

        $lastCommit = $this->commandRunner->run($repoPath, ['log', '-1', '--format=%h - %s (%cr)'], $gitError);
        if ($gitError !== '') {
            $gitErrors[] = $this->commandRunner->cleanError($gitError, $repoPath);
        }
        if ($lastCommit !== '') {
            $response['last_commit'] = $lastCommit;
        }

        return $this->withGitErrors($response, $gitErrors);
    }

    private function withGitErrors(array $response, array $gitErrors): array
    {
        $gitErrors = array_values(array_unique(array_filter($gitErrors)));
        if (!empty($gitErrors)) {
            $response['git_errors'] = $gitErrors;
        }

        return $response;
    }
}
