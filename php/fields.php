<?php
/**
 * Loads all form field components in the `Fields` namespace, so a page needs only one `require`:
 * ```php
 * require 'php/fields.php';
 * use Fields\Input;
 * use Fields\Textarea;
 * use Fields\Dropdown;
 * ```
 */
require_once __DIR__ . '/fields/Field.php';
require_once __DIR__ . '/fields/Input.php';
require_once __DIR__ . '/fields/Textarea.php';
require_once __DIR__ . '/fields/Dropdown.php';
