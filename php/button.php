<?php
/**
 * `Button` renders a design-system button (`.btn`, see css/components/buttons.css).
 * The button is echoed straight away from the constructor, so creating it inside a template prints it at that spot.
 *
 * Examples:
 * - `new Button("Save");`
 * - `new Button("Delete", style: "destructive", state: "loading");`
 * - `new Button("Send", type: "submit", attributes: ["id" => "send-btn"]);`
 *
 * The generated class names must stay in sync with the hand-written buttons in index.html.
 */
class Button {


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


    /** The HTML that was generated and echoed, kept so callers can reuse it. */
    public $html = null;


    /**
     * Builds the button HTML, stores it in `$html` and echoes it.
     * Pass named arguments (PHP 8+) so the optional parameters can be skipped.
     *
     * @param string $label
     * The button text. It is inserted as raw HTML, not escaped.
     *
     * @param string|null $style [optional]
     * One of `VALID_STYLES`, or `null` for the primary button.
     *
     * @param string|null $icon [optional]
     * The URL of an SVG icon (e.g., "/assets/icons/envelope-outline.svg").
     * It is shown before the label and left out while loading.
     *
     * @param string|null $state [optional]
     * One of `VALID_STATES`, or `null` for the default state.
     *
     * @param string $type [optional]
     * The HTML `type` attribute: "button" (default), "submit" or "reset".
     *
     * @param array $attributes [optional]
     * Extra HTML attributes as name => value (e.g., `["id" => "send-btn"]`).
     * Keys that match a state name are ignored, so use `$state` for those. Values are inserted as is, not escaped.
     *
     * @throws InvalidArgumentException if `$style` or `$state` is not an accepted value.
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


        // Build the class list
        $class = 'btn';
        if ($style) {
            $class .= " $style btn-$style";
        }

        // Build the extra attributes, skipping any that should be set through `$state`
        $attributesHtml = '';
        foreach ($attributes as $key => $value) {
            if (!in_array($key, self::VALID_STATES)) {
                $attributesHtml .= " $key='$value'";
            }
        }

        // Apply the state: "disabled" is a native attribute, the other states are classes
        if ($state == 'disabled') {
            $attributesHtml .= " disabled";
        } else {
            $class .= " $state";
        }

        // Add the icon before the label (not possible while loading)
        if ($icon && $state != 'loading') {
            $label = "<span class='.icon' style='--icon-src:url($icon)'></span> $label";
        }


        // Create, store and print the HTML
        $html = "<button type='$type' class='$class' $attributesHtml>";
        $html .= "$label</button>";
        $this->html = $html;
        echo $html;
    }
}
