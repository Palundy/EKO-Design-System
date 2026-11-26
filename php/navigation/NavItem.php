<?php
namespace EKO\DesignSystem\Navigation;

use InvalidArgumentException;
use EKO\DesignSystem\Component;


/**
 * `NavItem` renders one link of the main menu, followed by a yellow chevron (`.nav-item`, see css/components/nav-item.css).
 * It returns its HTML instead of printing it: print it with `<?= new NavItem(...) ?>` or `echo`,
 * or keep the string with `(string) new NavItem(...)`.
 * Import it with `use EKO\DesignSystem\Navigation\NavItem;` after requiring Composer's `vendor/autoload.php`.
 *
 * Examples:
 * - `echo new NavItem("Products", "/products");`
 * - `echo new NavItem("Products", "/products", active: true);` for the section the visitor is in.
 *
 * All text and attribute values are HTML-escaped.
 */
class NavItem extends Component {


    /**
     * Builds the navigation item HTML, stores it in `$html`.
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


        // Create and store the HTML
        $current = $active ? " aria-current=\"page\"" : "";
        $html = "<a class=\"nav-item\" href=\"" . self::escape($url) . "\"$current>" . self::escape($label) . "</a>";
        $this->html = $html;
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
