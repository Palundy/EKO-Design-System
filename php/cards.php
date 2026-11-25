<?php
/**
 * Loads all card components in the `Cards` namespace, so a page needs only one `require`:
 * ```php
 * require 'php/cards.php';
 * use Cards\ProductCard;
 * use Cards\ContactCard;
 * use Cards\ApplicationCard;
 * use Cards\IndustryCard;
 * use Cards\ArticleCard;
 * ```
 */
require_once __DIR__ . '/cards/Card.php';
require_once __DIR__ . '/cards/ProductCard.php';
require_once __DIR__ . '/cards/ContactCard.php';
require_once __DIR__ . '/cards/ImageCard.php';
require_once __DIR__ . '/cards/ApplicationCard.php';
require_once __DIR__ . '/cards/IndustryCard.php';
require_once __DIR__ . '/cards/ArticleCard.php';
