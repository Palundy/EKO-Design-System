<?php
namespace EKO\DesignSystem\Cards;

use InvalidArgumentException;
use EKO\DesignSystem\Component;
use EKO\DesignSystem\DesignSystem;


/**
 * `Card` is the shared base of the card components `ProductCard`, `ContactCard` and, through `ImageCard`,
 * the image cards (see assets/css/components/product-card.css, contact-card.css and image-card.css). It is not used directly.
 * The cards live in the `EKO\DesignSystem\Cards` namespace (e.g., `use EKO\DesignSystem\Cards\ProductCard;`).
 *
 * It holds what every card has in common: the escaping, icon and attribute helpers.
 * Like every `Component`, a card returns its HTML instead of printing it.
 */
abstract class Card extends Component {


    /**
     * Builds a decorative icon (see assets/css/components/icon.css). It is hidden from screen readers.
     *
     * @param string $icon
     * A bundled icon's name (e.g., "envelope-outline") or the URL of an SVG icon. See `DesignSystem::resolveIcon()`.
     *
     * @return string
     * The icon's HTML.
     *
     * @throws InvalidArgumentException if `$icon` names an unknown icon.
     */
    protected static function renderIcon($icon) {
        $style = "--icon-url: url('" . self::escapeCssString(DesignSystem::resolveIcon($icon)) . "')";
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
