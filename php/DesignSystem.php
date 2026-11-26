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
 * Composer's autoloader loads this class, and the components use it through the same namespace.
 */
class DesignSystem {


    /** The folder that holds the bundled icons on disk, used to check icon names. */
    private const ICON_FOLDER = __DIR__ . '/../assets/icons';

    /**
     * The pattern of an icon name (e.g., "envelope-outline"): lowercase letters and digits, separated by single hyphens.
     * Icon arguments that do not match it are treated as URLs.
     */
    private const ICON_NAME_PATTERN = '/^[a-z0-9]+(-[a-z0-9]+)*$/';


    /** The URL the design system's folder is served from. It always ends with a slash. */
    private static $_baseUrl = '/';


    /**
     * Sets the URL the design system's folder is served from.
     * This is the folder that contains `assets/`.
     *
     * @param string $baseUrl
     * A root-relative path (e.g., "/vendor/eko/design-system") or a full URL
     * (e.g., "https://intranet.example.com/design-system"). A trailing slash is optional.
     */
    public static function setBaseUrl($baseUrl) {
        self::$_baseUrl = rtrim((string) $baseUrl, '/') . '/';
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
     * Builds the URL of a bundled icon.
     *
     * @param string $name
     * The icon's file name without `.svg` (e.g., "envelope-outline"). See `assets/icons/` for the available icons.
     *
     * @return string
     * The icon's URL (e.g., "/assets/icons/envelope-outline.svg"). It is not HTML-escaped.
     *
     * @throws InvalidArgumentException if `$name` is not a valid icon name or the icon does not exist.
     */
    public static function icon($name) {
        if (!preg_match(self::ICON_NAME_PATTERN, (string) $name)) {
            throw new InvalidArgumentException("Invalid icon name: $name");
        }
        if (!is_file(self::ICON_FOLDER . "/$name.svg")) {
            throw new InvalidArgumentException("Unknown icon: $name");
        }
        return self::url("assets/icons/$name.svg");
    }


    /**
     * Turns a component's icon argument into a URL.
     * An icon name (e.g., "envelope-outline") becomes the bundled icon's URL, anything else is returned unchanged,
     * so components accept both names and URLs of your own icons (e.g., "/images/my-icon.svg").
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
        if (preg_match(self::ICON_NAME_PATTERN, (string) $icon)) {
            return self::icon($icon);
        }
        return (string) $icon;
    }


    /**
     * Builds the `<link>` tag for the design system's stylesheet.
     *
     * @return string
     * The tag's HTML (e.g., `<link rel="stylesheet" href="/assets/css/main.css">`).
     */
    public static function stylesheet() {
        return '<link rel="stylesheet" href="' . self::escape(self::url('assets/css/main.css')) . '">';
    }


    /**
     * Builds the `<script>` tags the components need (currently `assets/js/dropdowns.js`, for `Fields\Dropdown`).
     *
     * @return string
     * The tags' HTML.
     */
    public static function scripts() {
        return '<script src="' . self::escape(self::url('assets/js/dropdowns.js')) . '" defer></script>';
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
