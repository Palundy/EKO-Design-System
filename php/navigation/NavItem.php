<?php
namespace EKO\DesignSystem\Navigation;

use InvalidArgumentException;


/**
 * `NavItem` renders one link of the main menu, followed by a yellow chevron (`.nav-item`, see css/components/nav-item.css).
 * It is echoed straight away from the constructor, so creating it inside a template prints it at that spot.
 * Import it with `use EKO\DesignSystem\Navigation\NavItem;` after requiring Composer's `vendor/autoload.php`.
 *
 * Examples:
 * - `new NavItem("Products", "/products");`
 * - `new NavItem("Products", "/products", active: true);` for the section the visitor is in.
 *
 * All text and attribute values are HTML-escaped.
 */
class NavItem {


    /** The HTML that was generated and echoed, kept so callers can reuse it. */
    public $html = null;


    /**
     * Builds the navigation item HTML, stores it in `$html` and echoes it.
     *
     * @param string $label
     * The menu text.
     *
     * @param string $url
     * Where the item links to.
     *
     * @param bool $active [optional]
     * If set to `true`, marks the item as the current section: bold with a blue line underneath
     * (`aria-current="page"`).
     *
     * @throws InvalidArgumentException if `$label` or `$url` is empty.
     */
    public function __construct($label, $url, $active = false) {
        // Validate the arguments before building anything
        if ($label === null || $label === '' || $url === null || $url === '') {
            throw new InvalidArgumentException("A navigation item needs a label and a url");
        }


        // Create, store and print the HTML
        $current = $active ? " aria-current=\"page\"" : "";
        $html = "<a class=\"nav-item\" href=\"" . self::escape($url) . "\"$current>" . self::escape($label) . "</a>";
        $this->html = $html;
        echo $html;
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
