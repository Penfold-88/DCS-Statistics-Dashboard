<?php

namespace DcsStats\Services\Admin;

final class AdminDataService
{
    private AdminPlayerDataService $playerData;
    private AdminBanService $bans;
    private AdminExportResponder $exportResponder;

    public function __construct(
        ?AdminPlayerDataService $playerData = null,
        ?AdminBanService $bans = null,
        ?AdminExportResponder $exportResponder = null
    ) {
        $this->playerData = $playerData ?? new AdminPlayerDataService();
        $this->bans = $bans ?? new AdminBanService();
        $this->exportResponder = $exportResponder ?? new AdminExportResponder();
    }

    public function players($search = null, $limit = null, int $offset = 0): array
    {
        return $this->playerData->players($search, $limit, $offset);
    }

    public function playerStats($ucid): array
    {
        return $this->playerData->playerStats($ucid);
    }

    public function playerBans(bool $activeOnly = true): array
    {
        return $this->bans->playerBans($activeOnly);
    }

    public function playerIsBanned($ucid): bool
    {
        return $this->bans->playerIsBanned($ucid);
    }

    public function exportCsv($data, string $filename = 'export.csv'): void
    {
        $this->exportResponder->csv($data, $filename);
    }

    public function exportJson($data, string $filename = 'export.json'): void
    {
        $this->exportResponder->json($data, $filename);
    }
}
