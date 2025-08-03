<?php
/**
 * Admin panel updater utilities
 * Provides backup and update functionality with graceful
 * handling when the ZipArchive extension is unavailable.
 */

/**
 * Create a zip backup of the provided directory.
 *
 * @param string $source Directory to backup
 * @param string $destination Destination zip file
 * @return bool True on success, false on failure
 */
function createBackup(string $source, string $destination): bool {
    if (!class_exists('ZipArchive')) {
        echo "Error: PHP ZipArchive extension not installed. " .
             "Please enable the zip extension in php.ini or install the php-zip package. Update aborted.";
        return false;
    }

    $zip = new ZipArchive();
    if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        echo "Error: Unable to create backup archive.";
        return false;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        if (!$file->isDir()) {
            $filePath     = $file->getRealPath();
            $relativePath = substr($filePath, strlen($source) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();
    return true;
}

/**
 * Perform update by extracting provided archive into target directory.
 *
 * @param string $archivePath Path to update archive
 * @param string $targetDir Directory to extract to
 * @return bool True on success, false on failure
 */
function performUpdate(string $archivePath, string $targetDir): bool {
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($archivePath) === true) {
            $zip->extractTo($targetDir);
            $zip->close();
            return true;
        }
        echo "Error: Unable to open update archive.";
        return false;
    }

    // ZipArchive is unavailable – advise admin and attempt fallback
    echo "Warning: PHP ZipArchive extension not installed. " .
         "Consider enabling it via php.ini or installing php-zip. " .
         "Attempting fallback extraction...";

    // Fallback 1: PharData
    if (class_exists('PharData')) {
        try {
            $phar = new PharData($archivePath);
            $phar->extractTo($targetDir, null, true);
            return true;
        } catch (Exception $e) {
            // Continue to next fallback
        }
    }

    // Fallback 2: system unzip command
    $unzip = trim(shell_exec('command -v unzip'));
    if ($unzip) {
        $cmd = escapeshellcmd($unzip) . ' ' . escapeshellarg($archivePath) . ' -d ' . escapeshellarg($targetDir);
        exec($cmd, $output, $retval);
        if ($retval === 0) {
            return true;
        }
    }

    echo "\nError: No suitable archive extraction method available. Update aborted.";
    return false;
}
