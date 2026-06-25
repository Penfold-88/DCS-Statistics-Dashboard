<?php

use DcsStats\Core\TableResponsive;

function tableResponsiveStart($includeCards = true, $cardId = '') {
    TableResponsive::start((bool)$includeCards, (string)$cardId);
}

function tableResponsiveEnd($includeCards = true, $cardId = '') {
    TableResponsive::end((bool)$includeCards, (string)$cardId);
}

function tableResponsiveStyles() {
    TableResponsive::styles();
}

function createMobileCard($content, $classes = '') {
    return TableResponsive::mobileCard((string)$content, (string)$classes);
}

function tableResponsiveEscape($text) {
    return TableResponsive::escape($text);
}
