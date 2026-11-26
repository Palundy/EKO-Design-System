<?php
namespace EKO\DesignSystem\Fields;

use InvalidArgumentException;
use EKO\DesignSystem\Component;


/**
 * `Field` is the shared base of the form field components `Input`, `Textarea` and `Dropdown`
 * (see css/components/field.css). It is not used directly.
 *
 * It builds the parts every field has in common and leaves the control itself to the subclass:
 * ```html
 * <div class="field [error|success]">
 *     <label class="field-label" for="{id}">{label}</label>
 *     {control}
 *     <div id="{id}-info" class="field-info">{info}</div>
 * </div>
 * ```
 *
 * The fields live in the `EKO\DesignSystem\Fields` namespace (e.g., `use EKO\DesignSystem\Fields\Input;`).
 * Like every `Component`, a field returns its HTML instead of printing it.
 * All text and attribute values are HTML-escaped.
 */
abstract class Field extends Component {


    /**
     * States accepted by the `$state` argument:
     * - "error": red border and red info text (`.field.error`).
     * - "success": green border and green info text (`.field.success`).
     * - "disabled": adds the native `disabled` attribute to the control.
     */
    protected const VALID_STATES = [
        "error",
        "success",
        "disabled"
    ];


    /**
     * Checks the shared arguments, builds the wrapper, label and info text around the control
     * from `renderControl()`, stores the HTML in `$html`.
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
     * One of `VALID_STATES`, or `null` for the default state.
     *
     * @param string|null $info [optional]
     * Help or validation text shown under the control.
     *
     * @param bool $required [optional]
     * If set to `true`, adds the `required` attribute.
     *
     * @param array $attributes [optional]
     * Extra attributes for the control as name => value (e.g., `["autocomplete" => "email"]`).
     * `true` writes a bare attribute, `false` or `null` leaves it out.
     *
     * @throws InvalidArgumentException if `$state` is not an accepted value, or neither `$id` nor `$name` is given.
     */
    public function __construct(
        protected $label,
        protected $name = null,
        protected $id = null,
        protected $state = null,
        protected $info = null,
        protected $required = false,
        protected $attributes = []
    ) {
        // Validate the arguments before building anything
        if ($state && !in_array($state, self::VALID_STATES)) {
            throw new InvalidArgumentException("Invalid field state: $state");
        }
        $this->id = $id ?? $name;
        if (!$this->id) {
            throw new InvalidArgumentException("A field needs an id or a name: $label");
        }


        // Build the wrapper class: "disabled" is an attribute on the control, not a class
        $class = 'field';
        if ($state && $state !== 'disabled') {
            $class .= " $state";
        }

        // Build the label, marking required fields
        $labelText = self::escape($label) . ($required ? ' *' : '');


        // Create and store the HTML
        $html = "<div class=\"$class\">";
        $html .= "<label class=\"field-label\" for=\"" . self::escape($this->id) . "\">$labelText</label>";
        $html .= $this->renderControl();
        if ($this->hasInfo()) {
            $html .= "<div id=\"" . self::escape($this->infoId()) . "\" class=\"field-info\">" . self::escape($info) . "</div>";
        }
        $html .= "</div>";
        $this->html = $html;
    }



    /**
     * Builds the HTML of the form control itself (input, textarea or dropdown).
     *
     * @return string
     * The control's HTML.
     */
    abstract protected function renderControl();


    /**
     * Collects the attributes every control shares: class, id, name, required, disabled
     * and aria-describedby, followed by the caller's `$attributes`.
     *
     * @return array
     * The attributes as name => value, in the format `attributesToHtml()` expects.
     */
    protected function baseAttributes() {
        return array_merge([
            'class' => 'field-input',
            'id' => $this->id,
            'name' => $this->name,
            'required' => $this->required,
            'disabled' => $this->state === 'disabled',
            'aria-describedby' => $this->hasInfo() ? $this->infoId() : null,
        ], $this->attributes);
    }

    /**
     * Checks whether the field has info text to show.
     *
     * @return bool
     * `true` if `$info` is set and not empty.
     */
    protected function hasInfo() {
        return $this->info !== null && $this->info !== '';
    }

    /**
     * Builds the id of the info text element.
     *
     * @return string
     * The id in the form "{id}-info".
     */
    protected function infoId() {
        return $this->id . '-info';
    }


    /**
     * Turns name => value pairs into an escaped attribute string with a leading space.
     *
     * @param array $attributes
     * The attributes as name => value. `true` writes a bare attribute (e.g., ` required`), `false` and `null` are skipped.
     *
     * @return string
     * The attributes as HTML (e.g., ` id="name" required`).
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
}
