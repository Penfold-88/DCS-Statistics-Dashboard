<?php

namespace DcsStats\Core;

final class TableResponsive
{
    public static function start(bool $includeCards = true, string $cardId = ''): void
    {
        echo '<div class="table-wrapper">';
    }

    public static function end(bool $includeCards = true, string $cardId = ''): void
    {
        echo '</div>';

        if ($includeCards && $cardId !== '') {
            echo "\n<!-- Mobile Cards Container -->\n";
            echo '<div class="mobile-cards" id="' . htmlspecialchars($cardId) . '"></div>';
        }
    }

    public static function styles(): void
    {
        ?>
        <style>
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .mobile-cards {
            display: none;
        }

        @media screen and (max-width: 768px) {
            .table-wrapper {
                display: none;
            }

            .mobile-cards {
                display: block;
                padding: 0 10px;
            }

            .mobile-card {
                background: rgba(0, 0, 0, 0.6);
                border: 1px solid rgba(76, 175, 80, 0.3);
                border-radius: 12px;
                padding: 15px;
                margin-bottom: 15px;
                transition: all 0.3s ease;
            }

            .mobile-card:active {
                transform: scale(0.98);
                background: rgba(76, 175, 80, 0.1);
            }
        }
        </style>
        <?php
    }

    public static function mobileCard(string $content, string $classes = ''): string
    {
        return '<div class="mobile-card ' . htmlspecialchars($classes) . '">' . $content . '</div>';
    }

    public static function escape($text): string
    {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
}
