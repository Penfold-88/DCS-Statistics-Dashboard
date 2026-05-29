<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../admin_functions.php';
require_once dirname(__DIR__, 2) . '/dev_mode.php';

requireAdmin();
requirePermission('manage_updates');

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Only allow in dev mode
if (!isDevMode()) {
    echo json_encode(['success' => false, 'error' => 'Not in development mode']);
    exit;
}

$response = [
    'success' => false,
    'branch' => 'unknown',
    'ahead' => 0,
    'behind' => 0,
    'modified' => 0,
    'untracked' => 0
];

function runGitCommand($repoPath, $args, &$errorOutput = null) {
    $errorOutput = '';
    if (!is_dir($repoPath)) {
        return '';
    }

    $command = array_merge(['git'], $args);
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
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

function cleanGitStatusError($error, $repoPath) {
    $error = trim((string)$error);
    if ($error === '') {
        return '';
    }

    $error = str_replace(['\\', $repoPath], ['/', '[repo]'], $error);
    return substr($error, 0, 300);
}

// Check if we're in a git repository
$repoPath = realpath(dirname(__DIR__, 2));
$gitDir = $repoPath . '/.git';
if (!is_dir($gitDir)) {
    // Try parent directories (in case we're in a subdirectory)
    $checkDir = $repoPath;
    for ($i = 0; $i < 3; $i++) {
        $checkDir = dirname($checkDir);
        if (is_dir($checkDir . '/.git')) {
            $repoPath = realpath($checkDir);
            $gitDir = $checkDir . '/.git';
            break;
        }
    }
    
    if (!is_dir($gitDir)) {
        echo json_encode($response);
        exit;
    }
}

// Get current branch
$gitErrors = [];
$branch = runGitCommand($repoPath, ['rev-parse', '--abbrev-ref', 'HEAD'], $gitError);
if ($gitError !== '') {
    $gitErrors[] = cleanGitStatusError($gitError, $repoPath);
}
if ($branch) {
    $response['branch'] = $branch;
    $response['success'] = true;
    
    // Get ahead/behind counts
    $upstream = runGitCommand($repoPath, ['rev-parse', '--abbrev-ref', '--symbolic-full-name', '@{u}'], $gitError);
    if ($gitError !== '') {
        $gitErrors[] = cleanGitStatusError($gitError, $repoPath);
    }
    if ($upstream) {
        $counts = runGitCommand($repoPath, ['rev-list', '--left-right', '--count', 'HEAD...@{u}'], $gitError);
        if ($gitError !== '') {
            $gitErrors[] = cleanGitStatusError($gitError, $repoPath);
        }
        if ($counts) {
            list($ahead, $behind) = preg_split('/\s+/', $counts);
            $response['ahead'] = (int)$ahead;
            $response['behind'] = (int)$behind;
        }
    }
    
    // Get modified files count
    $modified = runGitCommand($repoPath, ['diff', '--name-only'], $gitError);
    if ($gitError !== '') {
        $gitErrors[] = cleanGitStatusError($gitError, $repoPath);
    }
    if ($modified) {
        $response['modified'] = count(array_filter(explode("\n", $modified)));
    }
    
    // Get staged files count
    $staged = runGitCommand($repoPath, ['diff', '--cached', '--name-only'], $gitError);
    if ($gitError !== '') {
        $gitErrors[] = cleanGitStatusError($gitError, $repoPath);
    }
    if ($staged) {
        $response['staged'] = count(array_filter(explode("\n", $staged)));
    }
    
    // Get untracked files count
    $untracked = runGitCommand($repoPath, ['ls-files', '--others', '--exclude-standard'], $gitError);
    if ($gitError !== '') {
        $gitErrors[] = cleanGitStatusError($gitError, $repoPath);
    }
    if ($untracked) {
        $response['untracked'] = count(array_filter(explode("\n", $untracked)));
    }
    
    // Get last commit info
    $lastCommit = runGitCommand($repoPath, ['log', '-1', '--format=%h - %s (%cr)'], $gitError);
    if ($gitError !== '') {
        $gitErrors[] = cleanGitStatusError($gitError, $repoPath);
    }
    if ($lastCommit) {
        $response['last_commit'] = $lastCommit;
    }
}

if (!empty($gitErrors)) {
    $response['git_errors'] = array_values(array_unique(array_filter($gitErrors)));
}

echo json_encode($response);
