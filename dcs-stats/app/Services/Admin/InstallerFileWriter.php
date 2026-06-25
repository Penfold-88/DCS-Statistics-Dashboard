<?php

namespace DcsStats\Services\Admin;

final class InstallerFileWriter
{
    public function writeAdminDataFiles(string $dataDir, array $files): ?string
    {
        foreach ($files as $filename => $content) {
            $path = $dataDir . '/' . $filename;
            if (!$this->writeJson($path, $content)) {
                return $filename;
            }
            chmod($path, 0600);
        }

        return null;
    }

    public function writeJson(string $path, array $content): bool
    {
        $json = json_encode($content, JSON_PRETTY_PRINT);
        return is_string($json) && file_put_contents($path, $json) !== false;
    }
}
