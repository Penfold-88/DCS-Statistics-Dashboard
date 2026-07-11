<?php

namespace DcsStats\Services\Admin;

final class ThemePresetActionService
{
    private ThemePresetStorageService $storageService;
    private ThemePresetService $presetService;

    public function __construct(
        ?ThemePresetStorageService $storageService = null,
        ?ThemePresetService $presetService = null
    ) {
        $this->storageService = $storageService ?? new ThemePresetStorageService();
        $this->presetService = $presetService ?? new ThemePresetService();
    }

    public function apply(array $post): array
    {
        $presetId = $post['preset_id'] ?? '';
        $presetType = $post['preset_type'] ?? 'built_in';
        $preset = $presetType === 'custom'
            ? $this->storageService->customPresetById($presetId)
            : ($this->presetService->builtInPresets()[$presetId] ?? null);

        if ($preset && $this->presetService->applyPreset($preset)) {
            return [
                'success' => true,
                'message' => 'Theme preset applied successfully',
                'log_message' => 'Applied theme preset: ' . ($preset['name'] ?? $presetId),
            ];
        }

        return [
            'success' => false,
            'message' => 'Theme preset could not be applied',
        ];
    }

    public function save(array $post): array
    {
        $presetName = trim($post['preset_name'] ?? '');
        $result = $this->presetService->saveCurrentPreset($presetName);
        if ($result['success']) {
            $result['log_message'] = 'Saved custom theme preset: ' . $presetName;
        }

        return $result;
    }

    public function delete(array $post): array
    {
        $presetIndex = (int)($post['preset_id'] ?? -1);
        $result = $this->presetService->deleteCustomPreset($presetIndex);
        if ($result['success']) {
            $result['log_message'] = 'Deleted custom theme preset: ' . ($result['deleted_name'] ?? 'Custom preset');
        }

        return $result;
    }
}
