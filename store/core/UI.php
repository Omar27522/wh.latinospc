<?php
// core/UI.php
/**
 * LatinosPC Store - UI Component & Template Helper Library
 * Clean, lightweight utility methods for store views.
 */

class UI {
    /**
     * Escape HTML output safely
     */
    public static function escape($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Shorthand for HTML escaping
     */
    public static function h($string) {
        return self::escape($string);
    }

    /**
     * Format currency amount ($XX.XX)
     */
    public static function formatPrice($amount) {
        return '$' . number_format((float)$amount, 2);
    }

    /**
     * Render an origin badge (Warehouse Shelf vs Store Custom)
     */
    public static function originBadge($locationCode, $isWarehouse = true) {
        if ($isWarehouse && !empty($locationCode)) {
            return '<span class="card-origin-badge" title="Physical Shelf">📦 ' . self::escape($locationCode) . '</span>';
        }
        return '<span class="card-origin-badge custom" title="Custom Item">🏪 Custom</span>';
    }

    /**
     * Render a sector badge
     */
    public static function sectorBadge($sector) {
        return '<span class="wh-sec-badge">' . self::escape($sector) . '</span>';
    }
}