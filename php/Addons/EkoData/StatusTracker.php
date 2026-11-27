<?php
namespace EKO\DesignSystem\Addons\EkoData;

use EKO\DesignSystem\Component;
use EKO\DesignSystem\DesignSystem;
use InvalidArgumentException;


/**
 * `StatusTracker` renders the steps of a process, such as a software deployment, with a progress bar underneath
 * (`.ekodata-status-tracker`, see assets/addons/eko-data/css/components/status-tracker.css). Part of the EKO Data addon,
 * so enable it first with `DesignSystem::enableAddon(EkoData::class)`.
 * It returns its HTML instead of printing it: print it with `<?= new StatusTracker(...) ?>` or `echo`,
 * or keep the string with `(string) new StatusTracker(...)`.
 * Import it with `use EKO\DesignSystem\Addons\EkoData\StatusTracker;` after requiring Composer's `vendor/autoload.php`.
 *
 * Example:
 * ```php
 * echo new StatusTracker(
 *     title: "Deployment status",
 *     description: "Track the deployment pipeline for this release.",
 *     icon: "list-ul-solid",
 *     action: new Button("Verify", state: "negative"),
 *     steps: [
 *         ["label" => "Release published", "status" => "done", "detail" => "2026-11-27, 11:47:27"],
 *         ["label" => "Workflow: Image build done", "status" => "active"],
 *         ["label" => "Deployment done!"],
 *     ]
 * );
 * ```
 *
 * The progress bar shows the share of steps that are done. A failed step colours it red.
 * All text and attribute values are HTML-escaped.
 */
class StatusTracker extends Component {


    /**
     * Step statuses accepted in a step's "status" key, each with the words screen readers hear before the label:
     * - "done": a filled marker.
     * - "active": in progress, a spinning ring. The step gets `aria-current="step"`.
     * - "pending": not started yet, an empty ring. The default.
     * - "error": failed, a red marker with an exclamation mark. The progress bar turns red.
     */
    private const VALID_STATUSES = [
        'done' => 'Done',
        'active' => 'In progress',
        'pending' => 'Pending',
        'error' => 'Failed',
    ];

    /** The keys a step accepts. */
    private const VALID_STEP_KEYS = [
        'label',
        'status',
        'detail',
    ];

    /** Heading levels accepted by the `$headingLevel` argument (`<h1>` to `<h6>`). */
    private const VALID_HEADING_LEVELS = [1, 2, 3, 4, 5, 6];


    /**
     * Builds the status tracker HTML, stores it in `$html`.
     * Pass named arguments (PHP 8+) so the optional parameters can be skipped.
     *
     * @param string $title
     * The title in the header (e.g., "Deployment status").
     *
     * @param array $steps
     * The steps in order, each an array with the keys:
     * - "label" (required): what the step is (e.g., "Release published").
     * - "status" [optional]: one of the keys of `VALID_STATUSES`, "pending" by default.
     * - "detail" [optional]: a short line under the label, such as the time it finished.
     *
     * @param string|null $description [optional]
     * A sentence under the header that explains what is tracked.
     *
     * @param string|null $icon [optional]
     * An icon before the title: a bundled icon's name (e.g., "list-ul-solid") or the URL of your own SVG icon.
     * See `DesignSystem::resolveIcon()`.
     *
     * @param Component|null $action [optional]
     * A component shown on the right of the header, usually an outlined `Button` (e.g., `new Button("Verify", state: "negative")`).
     * A core button is made smaller to fit the header.
     *
     * @param int $headingLevel [optional]
     * The heading element of the title: 2 (default), or 1 to 6 to fit the page outline. The look stays the same.
     *
     * @param array $attributes [optional]
     * Extra HTML attributes for the `<section>` as name => value (e.g., `["id" => "deployment-status"]`).
     * A "class" is added to the component's own classes. `true` writes a bare attribute, `false` or `null` leaves it out.
     *
     * @throws InvalidArgumentException if `$title` is empty, `$steps` is empty, a step has no label,
     * an unknown key or an unknown status, `$headingLevel` is not accepted, `$action` is not a component
     * or `$icon` names an unknown icon.
     */
    public function __construct(
        $title,
        $steps,
        $description = null,
        $icon = null,
        $action = null,
        $headingLevel = 2,
        $attributes = []
    ) {
        // Validate the arguments before building anything
        if ($title === null || $title === '') {
            throw new InvalidArgumentException("A status tracker needs a title");
        }
        if (!is_array($steps) || count($steps) === 0) {
            throw new InvalidArgumentException("A status tracker needs at least one step");
        }
        if (!in_array($headingLevel, self::VALID_HEADING_LEVELS, true)) {
            throw new InvalidArgumentException("Invalid status tracker heading level: $headingLevel");
        }
        if ($action !== null && !($action instanceof Component)) {
            throw new InvalidArgumentException("The status tracker action must be a component, such as a Button");
        }


        // Build the steps, counting the finished ones for the progress bar
        $done = 0;
        $hasError = false;
        $stepsHtml = '';
        foreach ($steps as $step) {
            $stepsHtml .= self::renderStep($step);
            $status = $step['status'] ?? 'pending';
            $done += $status === 'done' ? 1 : 0;
            $hasError = $hasError || $status === 'error';
        }
        $total = count($steps);


        // Collect the section's attributes, adding any extra class to the component's own
        $allAttributes = ['class' => 'ekodata-status-tracker' . ($hasError ? ' error' : '')];
        foreach ($attributes as $key => $value) {
            if ($key === 'class') {
                $allAttributes['class'] .= ' ' . $value;
            } else {
                $allAttributes[$key] = $value;
            }
        }

        // Build the header: the optional icon, the title and the optional action
        $html = "<section" . self::attributesToHtml($allAttributes) . ">";
        $html .= "<div class=\"ekodata-status-tracker-header\">";
        if ($icon !== null && $icon !== '') {
            $iconStyle = "--icon-url: url('" . self::escapeCssString(DesignSystem::resolveIcon($icon)) . "')";
            $html .= "<span class=\"icon\" style=\"" . self::escape($iconStyle) . "\" aria-hidden=\"true\"></span>";
        }
        $html .= "<h$headingLevel class=\"ekodata-status-tracker-title\">" . self::escape($title) . "</h$headingLevel>";
        if ($action !== null) {
            $html .= "<div class=\"ekodata-status-tracker-action\">$action</div>";
        }
        $html .= "</div>";

        // Build the body: the optional description, the steps and the progress bar
        $html .= "<div class=\"ekodata-status-tracker-body\">";
        if ($description !== null && $description !== '') {
            $html .= "<p class=\"ekodata-status-tracker-description\">" . self::escape($description) . "</p>";
        }
        $html .= "<ol class=\"ekodata-status-tracker-steps\">$stepsHtml</ol>";
        $html .= "<div class=\"ekodata-status-tracker-progress\" role=\"progressbar\" aria-label=\"Progress\""
            . " aria-valuemin=\"0\" aria-valuemax=\"$total\" aria-valuenow=\"$done\" aria-valuetext=\"$done of $total steps done\""
            . " style=\"--ekodata-status-tracker-done: $done; --ekodata-status-tracker-total: $total\">"
            . "<div class=\"ekodata-status-tracker-progress-bar\"></div></div>";
        $html .= "</div>";
        $html .= "</section>";


        // Store the HTML
        $this->html = $html;
    }



    /**
     * Builds the HTML of one step: the marker, the label (after its status in words, for screen readers) and the optional detail.
     *
     * @param mixed $step
     * The step, as described for the `$steps` argument of the constructor.
     *
     * @return string
     * The step's `<li>`.
     *
     * @throws InvalidArgumentException if the step is not an array, has no label, or has an unknown key or status.
     */
    private static function renderStep($step) {
        // Validate the step
        $label = is_array($step) ? ($step['label'] ?? null) : null;
        if ($label === null || $label === '') {
            throw new InvalidArgumentException("Every status tracker step needs a label");
        }
        foreach (array_keys($step) as $key) {
            if (!in_array($key, self::VALID_STEP_KEYS, true)) {
                throw new InvalidArgumentException("Unknown status tracker step key: $key");
            }
        }
        $status = $step['status'] ?? 'pending';
        if (!is_string($status) || !array_key_exists($status, self::VALID_STATUSES)) {
            throw new InvalidArgumentException("Invalid status tracker step status: $status");
        }
        $detail = $step['detail'] ?? null;


        // "done" and "error" are classes, the step in progress is marked with aria-current
        $class = 'ekodata-status-tracker-step';
        if ($status === 'done' || $status === 'error') {
            $class .= " $status";
        }
        $current = $status === 'active' ? " aria-current=\"step\"" : '';

        $html = "<li class=\"$class\"$current>";
        $html .= "<span class=\"ekodata-status-tracker-marker\" aria-hidden=\"true\"></span>";
        $html .= "<span class=\"ekodata-status-tracker-text\">";
        $html .= "<span class=\"ekodata-status-tracker-label\">"
            . "<span class=\"ekodata-status-tracker-status\">" . self::VALID_STATUSES[$status] . ": </span>"
            . self::escape($label) . "</span>";
        if ($detail !== null && $detail !== '') {
            $html .= "<span class=\"ekodata-status-tracker-detail\">" . self::escape($detail) . "</span>";
        }
        $html .= "</span>";
        $html .= "</li>";
        return $html;
    }


    /**
     * Turns name => value pairs into an escaped attribute string with a leading space.
     *
     * @param array $attributes
     * The attributes as name => value. `true` writes a bare attribute (e.g., ` hidden`), `false` and `null` are skipped.
     *
     * @return string
     * The attributes as HTML (e.g., ` class="ekodata-status-tracker" id="deployment-status"`).
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
