<?php
namespace Cards;


/**
 * `Card` is the shared base of the card components `ProductCard` and `ContactCard`
 * (see css/components/product-card.css and contact-card.css). It is not used directly.
 *
 * It holds what every card has in common: the generated HTML and the escaping helpers.
 * Like the other components, a card is echoed straight away from its constructor,
 * so creating one inside a template prints it at that spot.
 */
abstract class Card {


    /** The HTML that was generated and echoed, kept so callers can reuse it. */
    public $html = null;


    /**
     * Stores the HTML in `$html` and echoes it.
     *
     * @param string $html
     * The card's complete HTML.
     */
    protected function output($html) {
        $this->html = $html;
        echo $html;
    }

    /**
     * Builds a decorative icon (see css/components/icon.css). It is hidden from screen readers.
     *
     * @param string $url
     * The URL of the SVG icon (e.g., "/assets/icons/envelope-outline.svg").
     *
     * @return string
     * The icon's HTML.
     */
    protected static function renderIcon($url) {
        $style = "--icon-url: url('" . self::escapeCssString($url) . "')";
        return "<span class=\"icon\" style=\"" . self::escape($style) . "\" aria-hidden=\"true\"></span>";
    }

    /**
     * Turns name => value pairs into an escaped attribute string with a leading space.
     *
     * @param array $attributes
     * The attributes as name => value. `true` writes a bare attribute (e.g., ` disabled`), `false` and `null` are skipped.
     *
     * @return string
     * The attributes as HTML (e.g., ` type="button" disabled`).
     */
    protected static function attributesToHtml($attributes) {
        $html = '';
        foreach ($attributes as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            $html .= ' ' . self::escape($key);
            if ($value !== true) {
                $html .= '="' . self::escape($value) . '"';
            }
        }
        return $html;
    }

    /**
     * Checks whether an optional text value was given.
     *
     * @param mixed $value
     * The value to check.
     *
     * @return bool
     * `true` if `$value` is not `null` and not an empty string.
     */
    protected static function isFilled($value) {
        return $value !== null && $value !== '';
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
    protected static function escape($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escapes a value for use inside a single-quoted CSS string, such as `url('...')`.
     * Quotes, backslashes and line breaks are replaced by CSS escapes so the value cannot end the string.
     * The result still needs `escape()` when it is placed in an HTML attribute.
     *
     * @param mixed $value
     * The value to escape. It is converted to a string first.
     *
     * @return string
     * The escaped value.
     */
    protected static function escapeCssString($value) {
        return str_replace(
            ["\\", "'", '"', "\n", "\r"],
            ['\5c ', '\27 ', '\22 ', '\a ', '\d '],
            (string) $value
        );
    }
}
