<?php
/**
 * Loads all card components in the `Cards` namespace, so a page needs only one `require`:
 * ```php
 * require 'php/cards.php';
 * use Cards\ProductCard;
 * use Cards\ContactCard;
 * ```
 */
require_once __DIR__ . '/cards/Card.php';
require_once __DIR__ . '/cards/ProductCard.php';
require_once __DIR__ . '/cards/ContactCard.php';
