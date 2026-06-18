<?php

namespace DcsStats\Services\Admin;

final class GitCommandRunner
{
    public function run(string $repoPath, array $args, ?string &$errorOutput = null): string
    {
        $errorOutput = '';
        if (!is_dir($repoPath)) {
            return '';
        }

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open(array_merge(['git'], $args), $descriptorSpec, $pipes, $repoPath);
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

    public function cleanError(string $error, string $repoPath): string
    {
        $error = trim($error);
        if ($error === '') {
            return '';
        }

        $error = str_replace(['\\', $repoPath], ['/', '[repo]'], $error);
        return substr($error, 0, 300);
    }
}
