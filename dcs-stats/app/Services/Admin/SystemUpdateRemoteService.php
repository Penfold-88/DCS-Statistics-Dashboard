<?php

namespace DcsStats\Services\Admin;

final class SystemUpdateRemoteService
{
    private SystemUpdateGitHubClient $githubClient;

    public function __construct(?SystemUpdateGitHubClient $githubClient = null)
    {
        $this->githubClient = $githubClient ?? new SystemUpdateGitHubClient();
    }

    public function resolveRemoteBranch(string $repo, string $branch, ?string $specificVersion, string $apiUrl, callable $log): ?array
    {
        $remoteCommitSha = null;
        $remoteCommitDate = null;

        if ($specificVersion !== null) {
            $log('Resolving requested version to an immutable commit...');
            $commitResult = $this->githubClient->fetchCommit($repo, $specificVersion);
            $commitInfo = is_string($commitResult['data'] ?? null)
                ? json_decode($commitResult['data'], true)
                : null;
            $remoteCommitSha = is_array($commitInfo) ? ($commitInfo['sha'] ?? null) : null;
            $remoteCommitDate = is_array($commitInfo) ? ($commitInfo['commit']['committer']['date'] ?? null) : null;
            if (
                (int)($commitResult['http_code'] ?? 0) !== 200 ||
                !is_string($remoteCommitSha) ||
                preg_match('/^[a-f0-9]{40}$/i', $remoteCommitSha) !== 1
            ) {
                $log('Update cancelled: Requested version could not be verified on GitHub.');
                return null;
            }

            return [
                'branch' => $branch,
                'api_url' => 'https://api.github.com/repos/' . $repo . '/zipball/' . rawurlencode($remoteCommitSha),
                'commit_sha' => $remoteCommitSha,
                'commit_date' => $remoteCommitDate,
            ];
        }

        $log('Checking GitHub branch...');
        $branchResult = $this->githubClient->fetchBranch($repo, $branch);

        if ($branchResult['http_code'] !== 200 || !$branchResult['data']) {
            $log("Selected branch was not found: $branch");
            if ($branch === 'master') {
                $log('Trying fallback branch: main');
                $branch = 'main';
                $apiUrl = 'https://api.github.com/repos/' . $repo . '/zipball/' . rawurlencode($branch);
                $branchResult = $this->githubClient->fetchBranch($repo, $branch);
            }
        }

        if ($branchResult['http_code'] !== 200) {
            $log('Could not find a usable GitHub branch. Update cancelled.');
            $log('HTTP Code: ' . $branchResult['http_code']);
            if (!empty($branchResult['error'])) {
                $log('Connection Error: ' . $branchResult['error']);
            }
            return null;
        }

        if ($branchResult['data']) {
            $branchInfo = json_decode($branchResult['data'], true);
            $remoteCommitSha = $branchInfo['commit']['sha'] ?? null;
            $remoteCommitDate = $branchInfo['commit']['commit']['committer']['date'] ?? null;
            if (is_string($remoteCommitSha) && preg_match('/^[a-f0-9]{40}$/i', $remoteCommitSha) === 1) {
                $log('Latest branch commit: ' . substr($remoteCommitSha, 0, 12));
                $apiUrl = 'https://api.github.com/repos/' . $repo . '/zipball/' . rawurlencode($remoteCommitSha);
            } else {
                $log('Update cancelled: GitHub returned an invalid commit identifier.');
                return null;
            }
            if ($remoteCommitDate) {
                $log('Latest branch date: ' . date('Y-m-d H:i:s', strtotime($remoteCommitDate)));
            }
        }

        return [
            'branch' => $branch,
            'api_url' => $apiUrl,
            'commit_sha' => $remoteCommitSha,
            'commit_date' => $remoteCommitDate,
        ];
    }

    public function logLatestReleaseForSpecificVersion(string $repo, ?string $specificVersion, callable $log): void
    {
        if ($specificVersion === null) {
            return;
        }

        $log('Checking release information...');
        $latestReleaseTag = $this->githubClient->latestReleaseTag($repo);
        if ($latestReleaseTag !== null) {
            $log('Latest release: ' . $latestReleaseTag);
        }
    }
}
