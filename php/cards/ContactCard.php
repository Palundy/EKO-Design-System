<?php
namespace EKO\DesignSystem\Cards;

use InvalidArgumentException;


/**
 * `ContactCard` renders a contact card (`.contact-card`, see css/components/contact-card.css).
 * It returns its HTML instead of printing it: print it with `<?= new ContactCard(...) ?>` or `echo`,
 * or keep the string with `(string) new ContactCard(...)`.
 * Import it with `use EKO\DesignSystem\Cards\ContactCard;` after requiring Composer's `vendor/autoload.php`.
 *
 * Every part except the first line is optional and is left out when not given:
 * ```html
 * <div class="contact-card">
 *     <div class="card-header">
 *         <div class="card-image"><img src="{image}" alt="{imageAlt}"></div>
 *         <div class="card-contact-details">
 *             <div class="card-contact-details-primary">
 *                 <div class="card-contact-details-first">{first}</div>
 *                 <div class="card-contact-details-second">{second}</div>
 *                 <div class="card-contact-details-third">{third}</div>
 *             </div>
 *             <div class="card-contact-details-secondary">
 *                 <div class="card-contact-details-email">{icon} {email}</div>
 *                 <div class="card-contact-details-phone">{icon} {phone}</div>
 *                 <div class="card-contact-details-website">{icon} {website}</div>
 *             </div>
 *         </div>
 *     </div>
 *     <button class="card-button btn negative">{buttonIcon} {button}</button>
 * </div>
 * ```
 *
 * Examples:
 * - `echo new ContactCard("Palau van Helden");`
 * - `echo new ContactCard("Palau van Helden", second: "EKO Instruments B.V.", email: "info@eko-instruments.com");`
 * - `echo new ContactCard("Sales", phone: "+31 70 3050117", button: "Call us", buttonIcon: "call-outline");`
 *
 * All text and attribute values are HTML-escaped. The generated class names must stay in sync
 * with the hand-written contact card in index.html.
 */
class ContactCard extends Card {


    /** The icon shown before the email address. */
    private const EMAIL_ICON = 'envelope-outline';

    /** The icon shown before the phone number. */
    private const PHONE_ICON = 'call-outline';

    /** The icon shown before the website. */
    private const WEBSITE_ICON = 'internet-outline';


    /**
     * Builds the contact card HTML, stores it in `$html`.
     * Pass named arguments (PHP 8+) so the optional parameters can be skipped.
     *
     * @param string $first
     * The first line, shown in bold (usually the person's name).
     *
     * @param string|null $second [optional]
     * The second line (e.g., the company).
     *
     * @param string|null $third [optional]
     * The third line (e.g., the job title).
     *
     * @param string|null $email [optional]
     * The email address. Without it, the email row and its icon are left out.
     *
     * @param string|null $phone [optional]
     * The phone number. Without it, the phone row and its icon are left out.
     *
     * @param string|null $website [optional]
     * The website, shown as text (e.g., "www.eko-instruments.com"). Without it, the website row and its icon are left out.
     *
     * @param string|null $image [optional]
     * The URL of a photo, shown left of the details. Without it, the card has no image.
     *
     * @param string|null $imageAlt [optional]
     * The image's alt text. Defaults to `$first`.
     *
     * @param string|null $button [optional]
     * The label of the outlined button at the bottom of the card. Without it, the card has no button.
     *
     * @param string|null $buttonIcon [optional]
     * The icon shown before the button label: a bundled icon's name (e.g., "envelope-outline") or the URL of your own SVG icon.
     * Only used when `$button` is given.
     *
     * @param array $buttonAttributes [optional]
     * Extra HTML attributes for the button as name => value, to hook it up to your page
     * (e.g., `["id" => "contact-palau", "data-contact" => "palau"]`).
     * `true` writes a bare attribute, `false` or `null` leaves it out. `class` and `type` cannot be changed.
     *
     * @throws InvalidArgumentException if `$first` is empty.
     */
    public function __construct(
        $first,
        $second = null,
        $third = null,
        $email = null,
        $phone = null,
        $website = null,
        $image = null,
        $imageAlt = null,
        $button = null,
        $buttonIcon = null,
        $buttonAttributes = []
    ) {
        // Validate the arguments before building anything
        if (!self::isFilled($first)) {
            throw new InvalidArgumentException("A contact card needs a first line");
        }


        // Build the header: the optional image next to the details
        $html = "<div class=\"contact-card\">";
        $html .= "<div class=\"card-header\">";
        if (self::isFilled($image)) {
            $alt = $imageAlt ?? $first;
            $html .= "<div class=\"card-image\"><img src=\"" . self::escape($image) . "\" alt=\"" . self::escape($alt) . "\"></div>";
        }
        $html .= "<div class=\"card-contact-details\">";

        // Add the primary details: the first line and the optional second and third lines
        $html .= "<div class=\"card-contact-details-primary\">";
        $html .= "<div class=\"card-contact-details-first\">" . self::escape($first) . "</div>";
        if (self::isFilled($second)) {
            $html .= "<div class=\"card-contact-details-second\">" . self::escape($second) . "</div>";
        }
        if (self::isFilled($third)) {
            $html .= "<div class=\"card-contact-details-third\">" . self::escape($third) . "</div>";
        }
        $html .= "</div>";

        // Add the secondary details (email, phone and website), each as an icon and a clickable link.
        // Rows without a value are left out, and so is the whole block when no row has a value.
        $secondaryDetails = array_filter([
            'email' => [$email, self::EMAIL_ICON],
            'phone' => [$phone, self::PHONE_ICON],
            'website' => [$website, self::WEBSITE_ICON],
        ], fn($detail) => self::isFilled($detail[0]));
        if ($secondaryDetails) {
            $html .= "<div class=\"card-contact-details-secondary\">";
            foreach ($secondaryDetails as $kind => [$value, $icon]) {
                // Websites open in a new tab, `noopener` keeps that page from controlling this one
                $isWebsite = $kind === 'website';
                $link = "<a" . self::attributesToHtml([
                    'class' => 'link',
                    'href' => self::detailHref($kind, $value),
                    'target' => $isWebsite ? '_blank' : null,
                    'rel' => $isWebsite ? 'noopener' : null,
                ]) . ">" . self::escape($value) . "</a>";
                $html .= "<div class=\"card-contact-details-$kind\">" . self::renderIcon($icon) . " $link</div>";
            }
            $html .= "</div>";
        }
        $html .= "</div></div>";

        // Add the optional button below the header
        if (self::isFilled($button)) {
            $html .= self::renderButton($button, $buttonIcon, $buttonAttributes);
        }
        $html .= "</div>";


        // Store the HTML
        $this->html = $html;
    }

    /**
     * Builds the link target of a secondary detail, so the shown text can stay as the caller wrote it.
     * - "email": `mailto:` followed by the address.
     * - "phone": `tel:` followed by the number without spaces, dashes or brackets.
     *   For international numbers the "(0)" trunk prefix is dropped (e.g., "+31 (0)6 1234" becomes "tel:+3161234").
     * - "website": the URL as given when it starts with "http://" or "https://", otherwise "https://" is put in front.
     *
     * @param string $kind
     * The kind of detail: "email", "phone" or "website".
     *
     * @param string $value
     * The detail as shown on the card.
     *
     * @return string
     * The unescaped link target.
     */
    private static function detailHref($kind, $value) {
        $value = trim($value);
        switch ($kind) {
            case 'email':
                return "mailto:$value";
            case 'phone':
                if (str_starts_with($value, '+')) {
                    $value = str_replace('(0)', '', $value);
                }
                return 'tel:' . preg_replace('/(?!^\+)[^0-9]/', '', $value);
            default:
                return preg_match('#^https?://#i', $value) ? $value : "https://$value";
        }
    }

    /**
     * Builds the outlined button at the bottom of the card.
     *
     * @param string $label
     * The button label.
     *
     * @param string|null $icon
     * The URL of an SVG icon shown before the label, or `null` for no icon.
     *
     * @param array $attributes
     * Extra HTML attributes as name => value. `class` and `type` are ignored.
     *
     * @return string
     * The button's HTML.
     */
    private static function renderButton($label, $icon, $attributes) {
        unset($attributes['class'], $attributes['type']);
        $allAttributes = array_merge([
            'type' => 'button',
            'class' => 'card-button btn negative',
        ], $attributes);

        $content = self::escape($label);
        if (self::isFilled($icon)) {
            $content = self::renderIcon($icon) . " $content";
        }
        return "<button" . self::attributesToHtml($allAttributes) . ">$content</button>";
    }
}
