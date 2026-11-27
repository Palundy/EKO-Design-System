<?php
namespace EKO\DesignSystem;

use InvalidArgumentException;


/**
 * `DesignSystem` holds the settings that all components share: where the design system's static files
 * (the `assets/` folder with the CSS, JavaScript, fonts and icons) are served from. Every URL the components build to those files goes through it,
 * so an app can serve the design system from any folder, such as `/design-system/`.
 * `AssetPublisher` copies the files from the Composer package into such a folder.
 *
 * By default the files are expected at the web root (`/assets/css/main.css`, `/assets/icons/...`).
 * Set the base URL once, before any component is rendered:
 * ```php
 * require_once __DIR__ . '/vendor/autoload.php';
 *
 * use EKO\DesignSystem\DesignSystem;
 * use EKO\DesignSystem\Button;
 *
 * DesignSystem::setBaseUrl('/design-system');
 * ```
 *
 * Then use the helpers in the page head, and pass icon names to the components:
 * ```php
 * <head>
 *     <?= DesignSystem::stylesheet() ?>
 *     <?= DesignSystem::scripts() ?>
 * </head>
 * ...
 * echo new Button("Send", icon: "envelope-outline");
 * ```
 *
 * Addons (application-specific components, see `Addon`) are switched on here too, before the page head is printed.
 * `stylesheet()` and `scripts()` then also print the tags of every enabled addon:
 * ```php
 * use EKO\DesignSystem\Addons\EkoData\EkoData;
 *
 * DesignSystem::enableAddon(EkoData::class);
 * ```
 *
 * Composer's autoloader loads this class, and the components use it through the same namespace.
 */
class DesignSystem {


    /** The package root on disk (the folder that contains `assets/`), used to check that icons exist. */
    private const PACKAGE_ROOT = __DIR__ . '/..';

    /**
     * The pattern of an icon name (e.g., "envelope-outline"): lowercase letters and digits, separated by single hyphens.
     * Icon arguments that match neither this nor `ADDON_ICON_NAME_PATTERN` are treated as URLs.
     */
    private const ICON_NAME_PATTERN = '/^[a-z0-9]+(-[a-z0-9]+)*$/';

    /**
     * The pattern of an addon's icon name: the addon's name, a colon and the icon name (e.g., "eko-data:gauge-outline").
     * Group 1 is the addon, group 3 the icon.
     */
    private const ADDON_ICON_NAME_PATTERN = '/^([a-z0-9]+(-[a-z0-9]+)*):([a-z0-9]+(-[a-z0-9]+)*)$/';

    /** The pattern of a full URL with a scheme (e.g., "https://") or a protocol-relative URL ("//"). */
    private const ABSOLUTE_URL_PATTERN = '#^([a-z][a-z0-9+.-]*:)?//#i';


    /** The URL the design system's folder is served from. It always ends with a slash. */
    private static $_baseUrl = '/';

    /** The enabled addons, as name => descriptor class name, in the order they were enabled. */
    private static $_addons = [];


    /**
     * Sets the URL the design system's folder is served from.
     * This is the folder that contains `assets/`.
     *
     * A path is always made root-relative: "design-system" becomes "/design-system/".
     * A relative URL cannot work, because icon URLs are passed to the CSS in the `--icon-url` custom property,
     * and browsers resolve a relative URL in a custom property against the stylesheet that uses it (icon.css),
     * not against the page. The icon would then be looked for inside the CSS folder.
     *
     * @param string $baseUrl
     * A path from the web root (e.g., "/design-system"; the leading slash is optional), a full URL
     * (e.g., "https://intranet.example.com/design-system") or a protocol-relative URL (e.g., "//cdn.example.com/ds").
     * A trailing slash is optional.
     */
    public static function setBaseUrl($baseUrl) {
        $baseUrl = trim((string) $baseUrl);

        // Full and protocol-relative URLs are used as given, anything else is a path from the web root
        if (preg_match(self::ABSOLUTE_URL_PATTERN, $baseUrl)) {
            self::$_baseUrl = rtrim($baseUrl, '/') . '/';
            return;
        }
        $path = trim($baseUrl, '/');
        self::$_baseUrl = $path === '' ? '/' : "/$path/";
    }


    /**
     * Returns the URL the design system's folder is served from.
     *
     * @return string
     * The base URL, ending with a slash (e.g., "/vendor/eko/design-system/").
     */
    public static function getBaseUrl() {
        return self::$_baseUrl;
    }


    /**
     * Builds the URL of a file inside the design system's folder.
     *
     * @param string $path
     * The file's path inside the design system's folder (e.g., "assets/css/main.css").
     *
     * @return string
     * The file's URL (e.g., "/design-system/assets/css/main.css"). It is not HTML-escaped.
     */
    public static function url($path) {
        return self::$_baseUrl . ltrim((string) $path, '/');
    }


    /**
     * Switches an addon on: `stylesheet()` and `scripts()` then also print its tags.
     * Enabling the same addon twice has no further effect.
     *
     * @param string $addon
     * The addon's descriptor class, a subclass of `Addon` (e.g., `EkoData::class`).
     *
     * @throws InvalidArgumentException if `$addon` is not a subclass of `Addon` or has no valid `NAME`.
     */
    public static function enableAddon($addon) {
        if (!is_string($addon) || !is_subclass_of($addon, Addon::class)) {
            throw new InvalidArgumentException("Not a design system addon: " . (is_string($addon) ? $addon : gettype($addon)));
        }
        if (!preg_match(self::ICON_NAME_PATTERN, (string) $addon::NAME)) {
            throw new InvalidArgumentException("Invalid addon name: " . $addon::NAME);
        }
        self::$_addons[$addon::NAME] = $addon;
    }


    /**
     * Returns the enabled addons.
     *
     * @return array
     * The descriptor class names, in the order they were enabled.
     */
    public static function getAddons() {
        return array_values(self::$_addons);
    }


    /**
     * Builds the URL of a bundled icon: a core icon (e.g., "envelope-outline", from `assets/icons/`)
     * or an addon's icon, prefixed with the addon's name (e.g., "eko-data:gauge-outline", from `assets/addons/eko-data/icons/`).
     * An addon's icons can be used without enabling the addon.
     *
     * @param string $name
     * The icon's file name without `.svg`, with an addon prefix for an addon's icon.
     *
     * @return string
     * The icon's URL (e.g., "/assets/icons/envelope-outline.svg"). It is not HTML-escaped.
     *
     * @throws InvalidArgumentException if `$name` is not a valid icon name or the icon does not exist.
     */
    public static function icon($name) {
        $name = (string) $name;
        if (preg_match(self::ADDON_ICON_NAME_PATTERN, $name, $matches)) {
            $path = "assets/addons/{$matches[1]}/icons/{$matches[3]}.svg";
        } elseif (preg_match(self::ICON_NAME_PATTERN, $name)) {
            $path = "assets/icons/$name.svg";
        } else {
            throw new InvalidArgumentException("Invalid icon name: $name");
        }
        if (!is_file(self::PACKAGE_ROOT . "/$path")) {
            throw new InvalidArgumentException("Unknown icon: $name");
        }
        return self::url($path);
    }


    /**
     * Turns a component's icon argument into a URL.
     * An icon name (e.g., "envelope-outline" or "eko-data:gauge-outline") becomes the bundled icon's URL,
     * anything else is returned unchanged, so components accept both names and URLs of your own icons (e.g., "/images/my-icon.svg").
     *
     * @param string $icon
     * An icon name or a URL.
     *
     * @return string
     * The icon's URL. It is not HTML-escaped.
     *
     * @throws InvalidArgumentException if `$icon` looks like an icon name but the icon does not exist.
     */
    public static function resolveIcon($icon) {
        if (preg_match(self::ICON_NAME_PATTERN, (string) $icon) || preg_match(self::ADDON_ICON_NAME_PATTERN, (string) $icon)) {
            return self::icon($icon);
        }
        return (string) $icon;
    }


    /**
     * Builds the `<link>` tags for the design system's stylesheet, followed by those of every enabled addon.
     *
     * @return string
     * The tags' HTML, one per line (e.g., `<link rel="stylesheet" href="/assets/css/main.css">`).
     */
    public static function stylesheet() {
        $paths = ['assets/css/main.css'];
        foreach (self::$_addons as $addon) {
            foreach ($addon::STYLESHEETS as $file) {
                $paths[] = $addon::path($file);
            }
        }

        $tags = [];
        foreach ($paths as $path) {
            $tags[] = '<link rel="stylesheet" href="' . self::escape(self::url($path)) . '">';
        }
        return implode("\n", $tags);
    }


    /**
     * Builds the `<script>` tags the components need (`assets/js/dropdowns.js`, for `Fields\Dropdown`),
     * followed by those of every enabled addon.
     *
     * @return string
     * The tags' HTML, one per line.
     */
    public static function scripts() {
        $paths = ['assets/js/dropdowns.js'];
        foreach (self::$_addons as $addon) {
            foreach ($addon::SCRIPTS as $file) {
                $paths[] = $addon::path($file);
            }
        }

        $tags = [];
        foreach ($paths as $path) {
            $tags[] = '<script src="' . self::escape(self::url($path)) . '" defer></script>';
        }
        return implode("\n", $tags);
    }


    /**
     * Escapes a value for use in HTML text or a quoted attribute.
     *
     * @param mixed $value
     * The value to escape. It is converted to a string first.
     *
     * @return string
     * The escaped value.
     */
    private static function escape($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
