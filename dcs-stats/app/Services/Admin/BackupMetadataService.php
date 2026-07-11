<?php

namespace DcsStats\Services\Admin;

final class BackupMetadataService
{
    public function read(string $file, string $filename): array
    {
        $metadata = [
            'version' => 'Unknown',
            'branch' => 'Unknown',
        ];

        if (preg_match('/backup-\d{8}-\d{6}-([^-]+)-(.+)\.zip/', $filename, $matches)) {
            return [
                'branch' => $matches[1],
                'version' => str_replace('_', '.', $matches[2]),
            ];
        }

        if (!class_exists('ZipArchive')) {
            return $metadata;
        }

        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            return $metadata;
        }

        $metaIndex = $zip->locateName('.backup_meta.json');
        if ($metaIndex !== false) {
            $metaContent = $zip->getFromIndex($metaIndex);
            $meta = json_decode((string)$metaContent, true);
            if (is_array($meta)) {
                $metadata['version'] = $meta['version'] ?? 'Unknown';
                $metadata['branch'] = $meta['branch'] ?? 'Unknown';
            }
        }

        $zip->close();

        return $metadata;
    }
}
