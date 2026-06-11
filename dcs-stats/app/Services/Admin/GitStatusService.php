<?php

namespace DcsStats\Services\Admin;

final class GitStatusService
{
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

        $repoPath = $this->resolveRepoPath($dashboardRoot);
        if ($repoPath === null) {
            return $response;
        }

        $gitErrors = [];
        $branch = $this->runGitCommand($repoPath, ['rev-parse', '--abbrev-ref', 'HEAD'], $gitError);
        if ($gitError !== '') {
            $gitErrors[] = $this->cleanGitStatusError($gitError, $repoPath);
        }

        if ($branch === '') {
            return $this->withGitErrors($response, $gitErrors);
        }

        $response['branch'] = $branch;
        $response['success'] = true;

        $upstream = $this->runGitCommand($repoPath, ['rev-parse', '--abbrev-ref', '--symbolic-full-name', '@{u}'], $gitError);
        if ($gitError !== '') {
            $gitErrors[] = $this->cleanGitStatusError($gitError, $repoPath);
        }

        if ($upstream !== '') {
            $counts = $this->runGitCommand($repoPath, ['rev-list', '--left-right', '--count', 'HEAD...@{u}'], $gitError);
            if ($gitError !== '') {
                $gitErrors[] = $this->cleanGitStatusError($gitError, $repoPath);
            }
            if ($counts !== '') {
                [$ahead, $behind] = preg_split('/\s+/', $counts);
                $response['ahead'] = (int)$ahead;
                $response['behind'] = (int)$behind;
            }
        }

        $modified = $this->runGitCommand($repoPath, ['diff', '--name-only'], $gitError);
        if ($gitError !== '') {
            $gitErrors[] = $this->cleanGitStatusError($gitError, $repoPath);
        }
        if ($modified !== '') {
            $response['modified'] = count(array_filter(explode("\n", $modified)));
        }

        $staged = $this->runGitCommand($repoPath, ['diff', '--cached', '--name-only'], $gitError);
        if ($gitError !== '') {
            $gitErrors[] = $this->cleanGitStatusError($gitError, $repoPath);
        }
        if ($staged !== '') {
            $response['staged'] = count(array_filter(explode("\n", $staged)));
        }

        $untracked = $this->runGitCommand($repoPath, ['ls-files', '--others', '--exclude-standard'], $gitError);
        if ($gitError !== '') {
            $gitErrors[] = $this->cleanGitStatusError($gitError, $repoPath);
        }
        if ($untracked !== '') {
            $response['untracked'] = count(array_filter(explode("\n", $untracked)));
        }

        $lastCommit = $this->runGitCommand($repoPath, ['log', '-1', '--format=%h - %s (%cr)'], $gitError);
        if ($gitError !== '') {
            $gitErrors[] = $this->cleanGitStatusError($gitError, $repoPath);
        }
        if ($lastCommit !== '') {
            $response['last_commit'] = $lastCommit;
        }

        return $this->withGitErrors($response, $gitErrors);
    }

    private function resolveRepoPath(string $dashboardRoot): ?string
    {
        $candidates = [$dashboardRoot, dirname($dashboardRoot)];

        foreach ($candidates as $candidate) {
            $realPath = realpath($candidate);
            if (!$realPath || !$this->isAllowedGitRepoPath($realPath, $dashboardRoot)) {
                continue;
            }

            if (is_dir($realPath . '/.git') || is_file($realPath . '/.git')) {
                return $realPath;
            }
        }

        return null;
    }

    private function runGitCommand(string $repoPath, array $args, ?string &$errorOutput = null): string
    {
        $errorOutput = '';
        if (!is_dir($repoPath)) {
            return '';
        }

        $command = array_merge(['git'], $args);
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($command, $descriptorSpec, $pipes, $repoPath);
        if (!is_resource($process)) {
            return '';
        }

        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errorOutput = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return $exitCode === 0 ? trim($output) : '';
    }

    private function cleanGitStatusError(string $error, string $repoPath): string
    {
        $error = trim($error);
        if ($error === '') {
            return '';
        }

        $error = str_replace(['\\', $repoPath], ['/', '[repo]'], $error);
        return substr($error, 0, 300);
    }

    private function isAllowedGitRepoPath(string $repoPath, string $dashboardRoot): bool
    {
        return $repoPath === $dashboardRoot || $repoPath === dirname($dashboardRoot);
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

