<?php
namespace EKO\DesignSystem;


/**
 * `Addon` is the shared base of every addon descriptor. It is not used directly.
 *
 * An addon is a set of components that only one application needs (e.g., EKO Data). It sits next to the core:
 * its CSS, JavaScript and icons live in `assets/addons/<name>/`, its PHP classes in `php/Addons/<Name>/`
 * (namespace `EKO\DesignSystem\Addons\<Name>`). Addons may use the core, but the core never uses an addon,
 * and addons do not use each other.
 *
 * Each addon has one descriptor class that extends `Addon` and fills in its constants:
 * ```php
 * class EkoData extends Addon {
 *     public const NAME = 'eko-data';
 *     public const STYLESHEETS = ['css/eko-data.css'];
 * }
 * ```
 * An app switches an addon on once, before the page head is printed, so `DesignSystem::stylesheet()`
 * and `DesignSystem::scripts()` also print the addon's tags:
 * ```php
 * DesignSystem::enableAddon(EkoData::class);
 * ```
 * Import it with `use EKO\DesignSystem\Addon;` after requiring Composer's `vendor/autoload.php`.
 */
abstract class Addon {


    /**
     * The addon's name in kebab-case (e.g., "eko-data"). It is the addon's folder in `assets/addons/`,
     * its CSS sub-layer (`addons.<name>`) and the prefix of its icon names (e.g., "eko-data:gauge-outline").
     */
    public const NAME = '';

    /** The addon's stylesheets, relative to its folder (e.g., "css/eko-data.css"). Linked after the core stylesheet, in this order. */
    public const STYLESHEETS = [];

    /** The addon's scripts, relative to its folder (e.g., "js/charts.js"). Loaded after the core scripts, in this order. */
    public const SCRIPTS = [];


    /**
     * Builds the path of a file inside the addon's folder, relative to the design system's folder.
     * Pass it to `DesignSystem::url()` to get the file's URL.
     *
     * @param string $file
     * The file's path inside the addon's folder (e.g., "css/eko-data.css").
     *
     * @return string
     * The path (e.g., "assets/addons/eko-data/css/eko-data.css").
     */
    public static function path($file) {
        return 'assets/addons/' . static::NAME . '/' . ltrim((string) $file, '/');
    }
}
