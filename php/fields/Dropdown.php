<?php
namespace Fields;

use InvalidArgumentException;


/**
 * `Dropdown` renders a dropdown field: a native `<select>` inside `.dropdown`,
 * which js/dropdowns.js turns into the styled dropdown. Include that script on the page.
 * It is echoed straight away from the constructor, so creating it inside a template prints it at that spot.
 *
 * Example:
 * - `new Dropdown("Product of interest", name: "product", options: ["ms-80sh" => "MS-80SH", "ms-80" => "MS-80"], value: "ms-80", required: true);`
 *
 * The native `<select>` stays the source of truth: it holds the value and takes part in form submission and validation.
 */
class Dropdown extends Field {


    /**
     * Builds the dropdown field, stores the HTML in `$html` and echoes it.
     * Pass named arguments (PHP 8+) so the optional parameters can be skipped.
     *
     * @param string $label
     * The visible label text. " *" is added when `$required` is `true`.
     *
     * @param string|null $name [optional]
     * The form field name. Also used as the id when `$id` is not given.
     *
     * @param string|null $id [optional]
     * The element id, linked to the label and the info text. Defaults to `$name`.
     *
     * @param string|null $state [optional]
     * "error", "success", "disabled", or `null` for the default state.
     *
     * @param string|null $info [optional]
     * Help or validation text shown under the dropdown.
     *
     * @param bool $required [optional]
     * If set to `true`, adds the `required` attribute.
     *
     * @param array $attributes [optional]
     * Extra attributes for the `<select>` as name => value.
     * `true` writes a bare attribute, `false` or `null` leaves it out.
     *
     * @param array $options [optional]
     * The choices as value => visible text (e.g., `["ms-80" => "MS-80"]`).
     *
     * @param string|null $value [optional]
     * The value of the option that starts selected.
     *
     * @param string|null $placeholder [optional]
     * The text of the empty first option, which cannot be picked. Defaults to "Select".
     * It starts selected when `$value` is `null`. Pass `null` to leave it out.
     *
     * @throws InvalidArgumentException if `$state` is not an accepted value, or neither `$id` nor `$name` is given.
     */
    public function __construct(
        $label,
        $name = null,
        $id = null,
        $state = null,
        $info = null,
        $required = false,
        $attributes = [],
        protected $options = [],
        protected $value = null,
        protected $placeholder = 'Select'
    ) {
        parent::__construct($label, $name, $id, $state, $info, $required, $attributes);
    }


    /**
     * Builds the `.dropdown` wrapper with the `<select>` and its options.
     *
     * @return string
     * The dropdown's HTML.
     */
    protected function renderControl() {
        $html = '<div class="dropdown">';
        $html .= '<select' . self::attributesToHtml($this->baseAttributes()) . '>';

        // Add the placeholder option, selected when no value is chosen
        if ($this->placeholder !== null) {
            $html .= '<option' . self::attributesToHtml([
                'value' => '',
                'disabled' => true,
                'selected' => $this->value === null,
            ]) . '>' . self::escape($this->placeholder) . '</option>';
        }

        // Add the options, marking the one that matches `$value`
        foreach ($this->options as $optionValue => $text) {
            $html .= '<option' . self::attributesToHtml([
                'value' => $optionValue,
                'selected' => $this->value !== null && (string) $optionValue === (string) $this->value,
            ]) . '>' . self::escape($text) . '</option>';
        }

        $html .= '</select></div>';
        return $html;
    }
}
