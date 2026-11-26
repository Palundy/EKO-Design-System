<?php
namespace EKO\DesignSystem;

use InvalidArgumentException;


/**
 * `SectionHeading` renders the title above a page section, with an optional action link on the right
 * (`.section-heading`, see assets/css/components/section-heading.css).
 * It returns its HTML instead of printing it: print it with `<?= new SectionHeading(...) ?>` or `echo`,
 * or keep the string with `(string) new SectionHeading(...)`.
 * Import it with `use EKO\DesignSystem\SectionHeading;` after requiring Composer's `vendor/autoload.php`.
 *
 * Examples:
 * - `echo new SectionHeading("Popular Products");`
 * - `echo new SectionHeading("Popular Products", link: "View all", linkUrl: "/products");`
 *
 * All text and attribute values are HTML-escaped.
 */
class SectionHeading extends Component {


    /** Heading levels accepted by the `$headingLevel` argument (`<h1>` to `<h6>`). */
    private const VALID_HEADING_LEVELS = [1, 2, 3, 4, 5, 6];


    /**
     * Builds the section heading HTML, stores it in `$html`.
     * Pass named arguments (PHP 8+) so the optional parameters can be skipped.
     *
     * @param string $title
     * The section title.
     *
     * @param string|null $link [optional]
     * The text of the action link on the right (e.g., "View all"). An arrow is added after it.
     *
     * @param string|null $linkUrl [optional]
     * Where the link goes. Required when `$link` is given.
     *
     * @param int $headingLevel [optional]
     * The heading element of the title: 2 (default), or 1 to 6 to fit the page outline. The look stays the same.
     *
     * @throws InvalidArgumentException if `$title` is empty, `$headingLevel` is not accepted,
     * or `$link` is given without `$linkUrl`.
     */
    public function __construct(
        $title,
        $link = null,
        $linkUrl = null,
        $headingLevel = 2
    ) {
        // Validate the arguments before building anything
        if ($title === null || $title === '') {
            throw new InvalidArgumentException("A section heading needs a title");
        }
        if (!in_array($headingLevel, self::VALID_HEADING_LEVELS, true)) {
            throw new InvalidArgumentException("Invalid section heading level: $headingLevel");
        }
        $hasLink = $link !== null && $link !== '';
        if ($hasLink && ($linkUrl === null || $linkUrl === '')) {
            throw new InvalidArgumentException("The section heading link needs a linkUrl: $link");
        }


        // Build the title, followed by the optional link
        $html = "<div class=\"section-heading\">";
        $html .= "<h$headingLevel class=\"section-heading-title\">" . self::escape($title) . "</h$headingLevel>";
        if ($hasLink) {
            $html .= "<a class=\"link link-action\" href=\"" . self::escape($linkUrl) . "\">"
                . self::escape($link) . " <span aria-hidden=\"true\">→</span></a>";
        }
        $html .= "</div>";


        // Store the HTML
        $this->html = $html;
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
}
