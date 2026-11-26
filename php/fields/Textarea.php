<?php
namespace EKO\DesignSystem\Fields;

use InvalidArgumentException;


/**
 * `Textarea` renders a multi-line `<textarea>` field. Its height comes from `--field-textarea-height`.
 * It is echoed straight away from the constructor, so creating it inside a template prints it at that spot.
 * Import it with `use EKO\DesignSystem\Fields\Textarea;` after requiring Composer's `vendor/autoload.php`.
 *
 * Examples:
 * - `new Textarea("Message", name: "message", placeholder: "Your question", info: "Max. 500 characters.");`
 * - `new Textarea("Message", name: "message", state: "error", info: "Please fill in this field.");`
 */
class Textarea extends Field {


    /**
     * Builds the textarea field, stores the HTML in `$html` and echoes it.
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
     * Help or validation text shown under the textarea.
     *
     * @param bool $required [optional]
     * If set to `true`, adds the `required` attribute.
     *
     * @param array $attributes [optional]
     * Extra attributes for the textarea as name => value (e.g., `["maxlength" => 500]`).
     * `true` writes a bare attribute, `false` or `null` leaves it out.
     *
     * @param string|null $placeholder [optional]
     * The placeholder text.
     *
     * @param string|null $value [optional]
     * The initial content.
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
        protected $placeholder = null,
        protected $value = null
    ) {
        parent::__construct($label, $name, $id, $state, $info, $required, $attributes);
    }


    /**
     * Builds the `<textarea>` element.
     *
     * @return string
     * The textarea's HTML.
     */
    protected function renderControl() {
        $attributes = array_merge(['placeholder' => $this->placeholder], $this->baseAttributes());
        return '<textarea' . self::attributesToHtml($attributes) . '>'
            . self::escape($this->value ?? '')
            . '</textarea>';
    }
}
