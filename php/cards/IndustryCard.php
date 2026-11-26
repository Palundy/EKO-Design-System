<?php
namespace EKO\DesignSystem\Cards;

use InvalidArgumentException;


/**
 * `IndustryCard` renders a wide industry card (`.industry-card`, see assets/css/components/image-card.css):
 * a photo, a large title and a blue description.
 * It returns its HTML instead of printing it: print it with `<?= new IndustryCard(...) ?>` or `echo`,
 * or keep the string with `(string) new IndustryCard(...)`.
 * Import it with `use EKO\DesignSystem\Cards\IndustryCard;` after requiring Composer's `vendor/autoload.php`.
 *
 * With a `$url`, the whole card is a link (`<a class="industry-card">`), otherwise it is an `<article>`.
 *
 * Example:
 * ```php
 * echo new IndustryCard(
 *     title: "Solar Energy",
 *     description: "Precision instruments for solar resource assessment and PV performance monitoring.",
 *     image: "/assets/images/solar-energy.jpg",
 *     url: "/industries/solar-energy"
 * );
 * ```
 *
 * All text and attribute values are HTML-escaped.
 */
class IndustryCard extends ImageCard {


    /**
     * Builds the industry card HTML, stores it in `$html`.
     * Pass named arguments (PHP 8+) so the optional parameters can be skipped.
     *
     * @param string $title
     * The industry name.
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
     * @param string|null $url [optional]
     * Where the card links to. With a URL the whole card is clickable.
     *
     * @param int $headingLevel [optional]
     * The heading level of the title: 3 (default), or 2 to 6 to fit the page outline.
     *
     * @throws InvalidArgumentException if `$title` is empty or `$headingLevel` is not accepted.
     */
    public function __construct(
        $title,
        $description = null,
        $image = null,
        $imageAlt = null,
        $url = null,
        $headingLevel = 3
    ) {
        // Validate the arguments before building anything
        self::validate($title, $headingLevel);


        // Build the card as a link when there is a URL, otherwise as an article
        $open = self::isFilled($url)
            ? "<a class=\"industry-card\" href=\"" . self::escape($url) . "\">"
            : "<article class=\"industry-card\">";
        $close = self::isFilled($url) ? "</a>" : "</article>";

        $html = $open;
        $html .= self::renderImage($image, $imageAlt);
        $html .= "<div class=\"card-body\">";
        $html .= self::renderTitle($title, $headingLevel);
        $html .= self::renderDescription($description);
        $html .= "</div>" . $close;


        // Store the HTML
        $this->html = $html;
    }
}
