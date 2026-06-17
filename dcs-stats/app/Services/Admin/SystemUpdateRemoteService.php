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
            return [
                'branch' => $branch,
                'api_url' => $apiUrl,
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
                $apiUrl = "https://api.github.com/repos/$repo/zipball/$branch";
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
            if ($remoteCommitSha) {
                $log('Latest branch commit: ' . substr($remoteCommitSha, 0, 12));
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
