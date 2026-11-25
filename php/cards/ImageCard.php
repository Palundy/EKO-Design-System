<?php
namespace Cards;

use InvalidArgumentException;


/**
 * `ImageCard` is the shared base of the image cards `ApplicationCard`, `IndustryCard` and `ArticleCard`
 * (see css/components/image-card.css). It is not used directly.
 *
 * It builds the parts these cards have in common: the photo across the top, the title and the description.
 * Every image card has the same outline:
 * ```html
 * <article class="{card}">
 *     <div class="card-image"><img src="{image}" alt="{imageAlt}"></div>
 *     <div class="card-body">
 *         <h3 class="card-title">{title}</h3>
 *         <p class="card-description">{description}</p>
 *         …
 *     </div>
 * </article>
 * ```
 */
abstract class ImageCard extends Card {


    /** Heading levels accepted for the card title (`<h2>` to `<h6>`). */
    protected const VALID_HEADING_LEVELS = [2, 3, 4, 5, 6];


    /**
     * Checks the arguments every image card shares.
     *
     * @param string $title
     * The card title, which may not be empty.
     *
     * @param int $headingLevel
     * The heading level of the title, one of `VALID_HEADING_LEVELS`.
     *
     * @throws InvalidArgumentException if `$title` is empty or `$headingLevel` is not accepted.
     */
    protected static function validate($title, $headingLevel) {
        if (!self::isFilled($title)) {
            throw new InvalidArgumentException("An image card needs a title");
        }
        if (!in_array($headingLevel, self::VALID_HEADING_LEVELS, true)) {
            throw new InvalidArgumentException("Invalid image card heading level: $headingLevel");
        }
    }

    /**
     * Builds the image area. Without an image, the area shows the grey placeholder.
     *
     * @param string|null $image
     * The image URL, or `null` for the placeholder.
     *
     * @param string|null $imageAlt
     * The alt text. Defaults to an empty string, because the title next to the image already says what it shows.
     *
     * @return string
     * The image area's HTML.
     */
    protected static function renderImage($image, $imageAlt) {
        $html = "<div class=\"card-image\">";
        if (self::isFilled($image)) {
            $html .= "<img" . self::attributesToHtml([
                'src' => $image,
                'alt' => $imageAlt ?? '',
                'loading' => 'lazy',
            ]) . ">";
        }
        return $html . "</div>";
    }

    /**
     * Builds the title, optionally as a link.
     *
     * @param string $title
     * The title text.
     *
     * @param int $headingLevel
     * The heading level, 2 to 6.
     *
     * @param string|null $url [optional]
     * Where the title links to, or `null` for plain text.
     *
     * @return string
     * The title's HTML.
     */
    protected static function renderTitle($title, $headingLevel, $url = null) {
        $text = self::escape($title);
        if (self::isFilled($url)) {
            $text = "<a href=\"" . self::escape($url) . "\">$text</a>";
        }
        return "<h$headingLevel class=\"card-title\">$text</h$headingLevel>";
    }

    /**
     * Builds the description, or nothing when there is none.
     *
     * @param string|null $description
     * The description text.
     *
     * @return string
     * The description's HTML, or an empty string.
     */
    protected static function renderDescription($description) {
        if (!self::isFilled($description)) {
            return '';
        }
        return "<p class=\"card-description\">" . self::escape($description) . "</p>";
    }
}
