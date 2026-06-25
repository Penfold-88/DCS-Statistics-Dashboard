<?php

namespace DcsStats\Services\Admin;

final class AdminPresentationService
{
    public function formatDate($date, ?string $format = null): string
    {
        if (!$date) {
            return 'Never';
        }

        $timestamp = strtotime((string)$date);
        if (!$timestamp) {
            return 'Never';
        }

        return date($format ?: 'M d, Y H:i', $timestamp);
    }

    public function roleBadge($role): string
    {
        $roleNames = [
            ROLE_AIR_BOSS => ['name' => 'Air Boss', 'color' => '#ff4444', 'icon' => '✈️'],
            ROLE_LSO => ['name' => 'LSO', 'color' => '#2196F3', 'icon' => '🚦'],
        ];

        $info = $roleNames[$role] ?? ['name' => 'Unknown', 'color' => '#666', 'icon' => '❓'];

        return '<span style="display: inline-block; padding: 4px 8px; background-color: ' .
            $info['color'] . '; color: white; border-radius: 3px; font-size: 12px; font-weight: bold;">' .
            $info['icon'] . ' ' . $info['name'] . '</span>';
    }

    public function pagination($totalItems, $perPage, $currentPage, $baseUrl): string
    {
        $totalPages = ceil($totalItems / $perPage);
        if ($totalPages <= 1) {
            return '';
        }

        $html = '<div class="pagination">';

        if ($currentPage > 1) {
            $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="pagination-prev">Previous</a>';
        }

        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $currentPage) {
                $html .= '<span class="pagination-current">' . $i . '</span>';
            } else {
                $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination-link">' . $i . '</a>';
            }
        }

        if ($currentPage < $totalPages) {
            $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="pagination-next">Next</a>';
        }

        return $html . '</div>';
    }
}
