<?php
namespace EKO\DesignSystem\Cards;

use InvalidArgumentException;


/**
 * `ApplicationCard` renders an application card (`.application-card`, see css/components/image-card.css):
 * a photo, a title, a description and a "Learn More →" link.
 * It returns its HTML instead of printing it: print it with `<?= new ApplicationCard(...) ?>` or `echo`,
 * or keep the string with `(string) new ApplicationCard(...)`.
 * Import it with `use EKO\DesignSystem\Cards\ApplicationCard;` after requiring Composer's `vendor/autoload.php`.
 *
 * Example:
 * ```php
 * echo new ApplicationCard(
 *     title: "Solar Energy",
 *     description: "Resource assessment, performance monitoring and plant optimization.",
 *     image: "/assets/images/solar-energy.jpg",
 *     cta: "Learn More",
 *     ctaUrl: "/applications/solar-energy"
 * );
 * ```
 *
 * All text and attribute values are HTML-escaped.
 */
class ApplicationCard extends ImageCard {


    /**
     * Builds the application card HTML, stores it in `$html`.
     * Pass named arguments (PHP 8+) so the optional parameters can be skipped.
     *
     * @param string $title
     * The application name.
     *
     * @param string|null $description [optional]
     * A short text under the title.
     *
     * @param string|null $image [optional]
     * The image URL. Without it, the image area shows the grey placeholder.
     *
     * @param string|null $imageAlt [optional]
     * The image's alt text. Defaults to empty, because the title already describes the image.
     *
     * @param string|null $cta [optional]
     * The text of the link at the bottom of the card (e.g., "Learn More"). An arrow is added after it.
     *
     * @param string|null $ctaUrl [optional]
     * Where the link goes. Required when `$cta` is given.
     *
     * @param int $headingLevel [optional]
     * The heading level of the title: 3 (default), or 2 to 6 to fit the page outline.
     *
     * @throws InvalidArgumentException if `$title` is empty, `$headingLevel` is not accepted,
     * or `$cta` is given without `$ctaUrl`.
     */
    public function __construct(
        $title,
        $description = null,
        $image = null,
        $imageAlt = null,
        $cta = null,
        $ctaUrl = null,
        $headingLevel = 3
    ) {
        // Validate the arguments before building anything
        self::validate($title, $headingLevel);
        if (self::isFilled($cta) && !self::isFilled($ctaUrl)) {
            throw new InvalidArgumentException("The application card link needs a ctaUrl: $cta");
        }


        // Build the card: image, then the body with the title, description and link
        $html = "<article class=\"application-card\">";
        $html .= self::renderImage($image, $imageAlt);
        $html .= "<div class=\"card-body\">";
        $html .= self::renderTitle($title, $headingLevel);
        $html .= self::renderDescription($description);
        if (self::isFilled($cta)) {
            $html .= "<a class=\"card-cta link link-action\" href=\"" . self::escape($ctaUrl) . "\">"
                . self::escape($cta) . " <span aria-hidden=\"true\">→</span></a>";
        }
        $html .= "</div></article>";


        // Store the HTML
        $this->html = $html;
    }
}
