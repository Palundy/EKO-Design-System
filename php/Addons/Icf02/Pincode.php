<?php
namespace EKO\DesignSystem\Addons\Icf02;

use EKO\DesignSystem\Component;
use EKO\DesignSystem\DesignSystem;
use InvalidArgumentException;


/**
 * `Pincode` renders the ICF-02 sign-in card: a padlock, a dot per digit, a message and a numeric keypad
 * (`.icf02-pincode`, see assets/addons/icf-02/css/components/pincode.css). Part of the ICF-02 addon,
 * so enable it first with `DesignSystem::enableAddon(Icf02::class)`; `DesignSystem::scripts()` then also loads
 * assets/addons/icf-02/js/pincode.js, which handles the keypad, the keyboard's number keys and the animations.
 * It returns its HTML instead of printing it: print it with `<?= new Pincode(...) ?>` or `echo`,
 * or keep the string with `(string) new Pincode(...)`.
 * Import it with `use EKO\DesignSystem\Addons\Icf02\Pincode;` after requiring Composer's `vendor/autoload.php`.
 *
 * Example:
 * ```php
 * echo new Pincode(
 *     title: "ICF-02 · Unit 005",
 *     logo: "/static/images/eko-logo.svg",
 *     link: "Change pincode",
 *     attributes: ["id" => "login"]
 * );
 * ```
 * The app checks the pincode: listen for the `icf02-pincode-submit` event and call `event.detail.unlock()`
 * or `event.detail.reject()`. See the script's header comment.
 *
 * All text and attribute values are HTML-escaped.
 */
class Pincode extends Component {


    /**
     * States accepted by the `$state` argument:
     * - "locked": waiting for the pincode. The default.
     * - "error": the pincode was wrong. Red lock and dots, and the error message.
     * - "unlocked": the pincode was right. An open, green lock and dots, the unlocked message and a disabled keypad.
     */
    private const VALID_STATES = [
        'locked',
        'error',
        'unlocked',
    ];

    /** Pincode lengths accepted by the `$length` argument. */
    private const VALID_LENGTHS = [4, 5, 6, 7, 8];

    /** The padlock: a shackle that swings open, the body and its keyhole. */
    private const LOCK_SVG = '<svg viewBox="0 0 48 48" focusable="false">'
        . '<path class="icf02-pincode-shackle" d="M16 22v-7a8 8 0 0 1 16 0v7"/>'
        . '<rect class="icf02-pincode-body" x="10" y="21" width="28" height="21" rx="5"/>'
        . '<path class="icf02-pincode-keyhole" d="M24 27.5a3 3 0 0 1 1.5 5.6V36h-3v-2.9a3 3 0 0 1 1.5-5.6z"/>'
        . '</svg>';

    /** The icon on the delete key. */
    private const DELETE_ICON = 'backspace-outline';


    /**
     * Builds the pincode card HTML, stores it in `$html`.
     * Pass named arguments (PHP 8+) so the optional parameters can be skipped.
     *
     * @param string $title [optional]
     * The bold line at the top (e.g., "ICF-02 · Unit 005"), "ICF-02" by default.
     *
     * @param string|null $logo [optional]
     * The URL of a logo image above the title. It is used as given.
     *
     * @param string $logoAlt [optional]
     * The logo's alternative text, "EKO" by default.
     *
     * @param int $length [optional]
     * The number of digits, 4 to 8. 4 by default.
     *
     * @param string|null $message [optional]
     * The message while waiting for the pincode. By default "Enter your 4-digit pincode", with the length filled in.
     *
     * @param string $state [optional]
     * One of `VALID_STATES`: "locked" (default), "error" or "unlocked". The script changes it as the pincode is checked.
     *
     * @param string $errorMessage [optional]
     * The message after a wrong pincode, "Wrong pincode. Try again." by default.
     *
     * @param string $unlockedMessage [optional]
     * The message once unlocked, "Unlocked" by default.
     *
     * @param string $submitLabel [optional]
     * The text of the submit key, "Sign in" by default.
     *
     * @param string $deleteLabel [optional]
     * The name of the delete key for screen readers and as a tooltip, "Delete" by default.
     *
     * @param string|null $link [optional]
     * The text of a link under the keypad (e.g., "Change pincode").
     *
     * @param string|null $linkUrl [optional]
     * Where the link goes. Without it, the link is a `<button type="button">` for the app's own script.
     *
     * @param string $label [optional]
     * The accessible name of the card, "Sign in" by default.
     *
     * @param array $attributes [optional]
     * Extra HTML attributes for the card's `<div>` as name => value (e.g., `["id" => "login"]`).
     * A "class" is added to the component's own classes. `true` writes a bare attribute, `false` or `null` leaves it out.
     *
     * @throws InvalidArgumentException if `$title` is empty, `$length` or `$state` is not accepted,
     * or `$linkUrl` is given without `$link`.
     */
    public function __construct(
        $title = 'ICF-02',
        $logo = null,
        $logoAlt = 'EKO',
        $length = 4,
        $message = null,
        $state = 'locked',
        $errorMessage = 'Wrong pincode. Try again.',
        $unlockedMessage = 'Unlocked',
        $submitLabel = 'Sign in',
        $deleteLabel = 'Delete',
        $link = null,
        $linkUrl = null,
        $label = 'Sign in',
        $attributes = []
    ) {
        // Validate the arguments before building anything
        if ($title === null || $title === '') {
            throw new InvalidArgumentException("A pincode needs a title");
        }
        if (!in_array($length, self::VALID_LENGTHS, true)) {
            throw new InvalidArgumentException("Invalid pincode length: $length");
        }
        if (!in_array($state, self::VALID_STATES, true)) {
            throw new InvalidArgumentException("Invalid pincode state: $state");
        }
        $hasLink = $link !== null && $link !== '';
        if (!$hasLink && $linkUrl !== null && $linkUrl !== '') {
            throw new InvalidArgumentException("A pincode linkUrl needs a link text");
        }
        if ($message === null || $message === '') {
            $message = "Enter your $length-digit pincode";
        }


        // The message and the dots follow the state: a checked pincode shows all its dots
        $messages = ['locked' => $message, 'error' => $errorMessage, 'unlocked' => $unlockedMessage];
        $filled = $state === 'locked' ? 0 : $length;

        // Collect the card's attributes, adding any extra class to the component's own
        $allAttributes = [
            'class' => 'icf02-pincode' . ($state === 'locked' ? '' : " $state"),
            'role' => 'group',
            'aria-label' => $label,
            'data-length' => $length,
            'data-message' => $message,
            'data-error-message' => $errorMessage,
            'data-unlocked-message' => $unlockedMessage,
        ];
        foreach ($attributes as $key => $value) {
            if ($key === 'class') {
                $allAttributes['class'] .= ' ' . $value;
            } else {
                $allAttributes[$key] = $value;
            }
        }

        // Build the head: the optional logo, the title and the padlock
        $html = "<div" . self::attributesToHtml($allAttributes) . ">";
        if ($logo !== null && $logo !== '') {
            $html .= "<img class=\"icf02-pincode-logo\" src=\"" . self::escape($logo) . "\" alt=\"" . self::escape($logoAlt) . "\">";
        }
        $html .= "<span class=\"icf02-pincode-title\">" . self::escape($title) . "</span>";
        $html .= "<span class=\"icf02-pincode-lock\" aria-hidden=\"true\">" . self::LOCK_SVG . "</span>";

        // Build the dots, each with its index so their animations can follow one another
        $html .= "<div class=\"icf02-pincode-dots\" role=\"img\" aria-label=\"$filled of $length digits entered\">";
        for ($index = 0; $index < $length; $index++) {
            $html .= "<span class=\"icf02-pincode-dot" . ($index < $filled ? ' filled' : '') . "\""
                . " style=\"--icf02-pincode-dot-index: $index\"></span>";
        }
        $html .= "</div>";
        $html .= "<p class=\"icf02-pincode-message\" role=\"status\">" . self::escape($messages[$state]) . "</p>";

        // Build the keypad: 1 to 9, then delete, 0 and submit. An unlocked card takes no more input
        $disabled = $state === 'unlocked' ? ' disabled' : '';
        $html .= "<div class=\"icf02-pincode-keys\">";
        foreach (['1', '2', '3', '4', '5', '6', '7', '8', '9'] as $digit) {
            $html .= "<button class=\"icf02-pincode-key\" type=\"button\" data-key=\"$digit\"$disabled>$digit</button>";
        }
        $deleteIcon = "--icon-url: url('" . self::escapeCssString(DesignSystem::resolveIcon(self::DELETE_ICON)) . "')";
        $html .= "<button class=\"icf02-pincode-key icf02-pincode-key-delete\" type=\"button\" data-key=\"delete\""
            . " aria-label=\"" . self::escape($deleteLabel) . "\" title=\"" . self::escape($deleteLabel) . "\"$disabled>"
            . "<span class=\"icon\" style=\"" . self::escape($deleteIcon) . "\" aria-hidden=\"true\"></span></button>";
        $html .= "<button class=\"icf02-pincode-key\" type=\"button\" data-key=\"0\"$disabled>0</button>";
        $html .= "<button class=\"icf02-pincode-key icf02-pincode-key-submit\" type=\"button\" data-key=\"enter\"$disabled>"
            . self::escape($submitLabel) . "</button>";
        $html .= "</div>";

        // Build the optional link under the keypad
        if ($hasLink) {
            if ($linkUrl !== null && $linkUrl !== '') {
                $html .= "<a class=\"link icf02-pincode-link\" href=\"" . self::escape($linkUrl) . "\">" . self::escape($link) . "</a>";
            } else {
                $html .= "<button class=\"link icf02-pincode-link\" type=\"button\">" . self::escape($link) . "</button>";
            }
        }
        $html .= "</div>";


        // Store the HTML
        $this->html = $html;
    }



    /**
     * Turns name => value pairs into an escaped attribute string with a leading space.
     *
     * @param array $attributes
     * The attributes as name => value. `true` writes a bare attribute (e.g., ` hidden`), `false` and `null` are skipped.
     *
     * @return string
     * The attributes as HTML (e.g., ` class="icf02-pincode" id="login"`).
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
