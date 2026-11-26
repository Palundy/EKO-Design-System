<?php
namespace EKO\DesignSystem;

use InvalidArgumentException;


/**
 * `Button` renders a design-system button (`.btn`, see css/components/button.css).
 * It returns its HTML instead of printing it: print it with `<?= new Button(...) ?>` or `echo`,
 * or keep the string with `(string) new Button(...)`.
 * Import it with `use EKO\DesignSystem\Button;` after requiring Composer's `vendor/autoload.php`.
 *
 * Examples:
 * - `echo new Button("Save");`
 * - `echo new Button("Delete", style: "destructive", state: "loading");`
 * - `echo new Button("Send", type: "submit", attributes: ["id" => "send-btn"]);`
 *
 * The generated class names must stay in sync with the hand-written buttons in index.html.
 */
class Button extends Component {


    /**
     * Colour variants accepted by the `$style` argument. Each one maps to a `.btn-<style>` class.
     * Leave `$style` empty for the default (primary) button.
     */
    private const VALID_STYLES = [
        "destructive",
        "positive",
        "enquiry"
    ];

    /**
     * States accepted by the `$state` argument:
     * - "disabled": adds the native `disabled` attribute.
     * - "negative": outlined button (white background, coloured border and text).
     * - "loading": shows a CSS spinner. Any icon is left out.
     */
    private const VALID_STATES = [
        "disabled",
        "negative",
        "loading"
    ];


    /**
     * Builds the button HTML, stores it in `$html`.
     * Pass named arguments (PHP 8+) so the optional parameters can be skipped.
     * All text and attribute values are HTML-escaped, so user input can be passed safely.
     *
     * @param string $label
     * The button text. It is escaped, so it is shown literally and cannot contain HTML.
     *
     * @param string|null $style [optional]
     * One of `VALID_STYLES`, or `null` for the primary button.
     *
     * @param string|null $icon [optional]
     * A bundled icon's name (e.g., "envelope-outline") or the URL of your own SVG icon.
     * It is shown before the label and left out while loading. See `DesignSystem::resolveIcon()`.
     *
     * @param string|null $state [optional]
     * One of `VALID_STATES`, or `null` for the default state.
     *
     * @param string $type [optional]
     * The HTML `type` attribute: "button" (default), "submit" or "reset".
     *
     * @param array $attributes [optional]
     * Extra HTML attributes as name => value (e.g., `["id" => "send-btn"]`).
     * `true` writes a bare attribute, `false` or `null` leaves it out.
     * Keys that match a state name are ignored, so use `$state` for those.
     *
     * @throws InvalidArgumentException if `$style` or `$state` is not an accepted value, or `$icon` names an unknown icon.
     */
    public function __construct(
        $label,
        $style = null,
        $icon = null,
        $state = null,
        $type = 'button',
        $attributes = []
    ) {
        // Validate the style and state before building anything
        if ($style && !in_array($style, self::VALID_STYLES)) {
            throw new InvalidArgumentException("Invalid button style: $style");
        }
        if ($state && !in_array($state, self::VALID_STATES)) {
            throw new InvalidArgumentException("Invalid button state: $state");
        }


        // Build the class list: "disabled" is a native attribute, the other states are classes
        $class = 'btn';
        if ($style) {
            $class .= " $style btn-$style";
        }
        if ($state && $state !== 'disabled') {
            $class .= " $state";
        }

        // Collect the attributes, skipping any that should be set through `$state`
        $allAttributes = [
            'type' => $type,
            'class' => $class,
        ];
        foreach ($attributes as $key => $value) {
            if (!in_array($key, self::VALID_STATES)) {
                $allAttributes[$key] = $value;
            }
        }
        $allAttributes['disabled'] = $state === 'disabled';

        // Build the content: the escaped label, with the icon before it (not possible while loading)
        $content = self::escape($label);
        if ($icon && $state !== 'loading') {
            $iconStyle = "--icon-url: url('" . self::escapeCssString(DesignSystem::resolveIcon($icon)) . "')";
            $content = "<span class=\"icon\" style=\"" . self::escape($iconStyle) . "\" aria-hidden=\"true\"></span> $content";
        }


        // Create and store the HTML
        $html = "<button" . self::attributesToHtml($allAttributes) . ">$content</button>";
        $this->html = $html;
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
    private static function attributesToHtml($attributes) {
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
    private static function escapeCssString($value) {
        return str_replace(
            ["\\", "'", '"', "\n", "\r"],
            ['\5c ', '\27 ', '\22 ', '\a ', '\d '],
            (string) $value
        );
    }
}
