<?php
namespace EKO\DesignSystem\Fields;

use InvalidArgumentException;


/**
 * `Input` renders a single-line `<input>` field.
 * It is echoed straight away from the constructor, so creating it inside a template prints it at that spot.
 *
 * Examples:
 * - `new Input("Full Name", name: "name", placeholder: "John Doe", required: true);`
 * - `new Input("E-mail", name: "email", type: "email", state: "error", info: "Please enter a valid e-mail address.");`
 */
class Input extends Field {


    /**
     * Builds the input field, stores the HTML in `$html` and echoes it.
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
     * Help or validation text shown under the input.
     *
     * @param bool $required [optional]
     * If set to `true`, adds the `required` attribute.
     *
     * @param array $attributes [optional]
     * Extra attributes for the input as name => value (e.g., `["autocomplete" => "email"]`).
     * `true` writes a bare attribute, `false` or `null` leaves it out.
     *
     * @param string $type [optional]
     * The HTML input type: "text" (default), "email", "tel", "number", etc.
     *
     * @param string|null $placeholder [optional]
     * The placeholder text.
     *
     * @param string|null $value [optional]
     * The initial value.
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
        protected $type = 'text',
        protected $placeholder = null,
        protected $value = null
    ) {
        parent::__construct($label, $name, $id, $state, $info, $required, $attributes);
    }


    /**
     * Builds the `<input>` element.
     *
     * @return string
     * The input's HTML.
     */
    protected function renderControl() {
        $attributes = array_merge([
            'type' => $this->type,
            'placeholder' => $this->placeholder,
            'value' => $this->value,
        ], $this->baseAttributes());
        return '<input' . self::attributesToHtml($attributes) . ' />';
    }
}
