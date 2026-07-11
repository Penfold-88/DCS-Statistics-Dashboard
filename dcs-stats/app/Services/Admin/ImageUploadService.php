<?php

namespace DcsStats\Services\Admin;

final class ImageUploadService
{
    private const ALLOWED_IMAGE_TYPES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
    ];

    public function upload(?array $uploadedFile, string $targetBaseName, int $maxBytes, string $label, bool $required): array
    {
        if (!$uploadedFile || ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $required
                ? ['success' => false, 'message' => $label . ' is required']
                : ['success' => true, 'path' => ''];
        }

        if (($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Failed to upload ' . strtolower($label)];
        }

        $tmpPath = $uploadedFile['tmp_name'];
        $fileSize = $uploadedFile['size'];
        $fileType = mime_content_type($tmpPath);
        $extension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));

        if (!isset(self::ALLOWED_IMAGE_TYPES[$extension]) || self::ALLOWED_IMAGE_TYPES[$extension] !== $fileType) {
            return ['success' => false, 'message' => 'Please upload a JPG, PNG, or WebP ' . strtolower(str_replace('Header ', '', $label))];
        }

        if ($fileSize > $maxBytes) {
            $limit = $maxBytes >= 5242880 ? '5MB' : '2MB';
            return ['success' => false, 'message' => $label . ' must be less than ' . $limit];
        }

        $uploadDir = DCS_ROOT_PATH . '/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetName = $targetBaseName . '.' . ($extension === 'jpeg' ? 'jpg' : $extension);
        $targetPath = $uploadDir . '/' . $targetName;
        if (!move_uploaded_file($tmpPath, $targetPath)) {
            return ['success' => false, 'message' => 'Failed to upload ' . strtolower($label)];
        }

        return ['success' => true, 'path' => 'uploads/' . $targetName];
    }
}
