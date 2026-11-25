<?php
/**
 * Loads all navigation components in the `Navigation` namespace, so a page needs only one `require`:
 * ```php
 * require 'php/navigation.php';
 * use Navigation\Breadcrumb;
 * use Navigation\NavItem;
 * ```
 */
require_once __DIR__ . '/navigation/Breadcrumb.php';
require_once __DIR__ . '/navigation/NavItem.php';
