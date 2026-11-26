<?php
namespace EKO\DesignSystem\Cards;

use InvalidArgumentException;


/**
 * `ArticleCard` renders a blog or news card (`.article-card`, see css/components/image-card.css):
 * a photo, a category tag, a title, a description, and a footer with the reading time and share buttons.
 * It is echoed straight away from the constructor, so creating it inside a template prints it at that spot.
 * Import it with `use EKO\DesignSystem\Cards\ArticleCard;` after requiring Composer's `vendor/autoload.php`.
 *
 * The title and description are cut off after three lines.
 *
 * Example:
 * ```php
 * new ArticleCard(
 *     title: "How to choose the right pyranometer",
 *     url: "https://www.eko-instruments.com/blog/choosing-a-pyranometer",
 *     tag: "Instruments",
 *     description: "A short introduction to the ISO 9060 classes and what they mean for your project.",
 *     image: "/assets/images/blog/pyranometer.jpg",
 *     readTime: "6 minutes read",
 *     share: true
 * );
 * ```
 *
 * All text and attribute values are HTML-escaped.
 */
class ArticleCard extends ImageCard {


    /**
     * The share buttons, in display order: label => [share URL pattern, icon].
     * In the pattern, `{url}` is replaced by the article URL and `{title}` by the title, both URL-encoded.
     */
    private const SHARE_TARGETS = [
        'LinkedIn' => ['https://www.linkedin.com/sharing/share-offsite/?url={url}', 'linkedin-alt-monochrome'],
        'X' => ['https://twitter.com/intent/tweet?url={url}&text={title}', 'twitter-monochrome'],
        'Facebook' => ['https://www.facebook.com/sharer/sharer.php?u={url}', 'facebook-f-monochrome'],
        'email' => ['mailto:?subject={title}&body={url}', 'envelope-outline'],
    ];


    /**
     * Builds the article card HTML, stores it in `$html` and echoes it.
     * Pass named arguments (PHP 8+) so the optional parameters can be skipped.
     *
     * @param string $title
     * The article title. It is cut off after three lines.
     *
     * @param string|null $url [optional]
     * The article URL. The title links to it, and the share buttons share it.
     * Use a full URL (with `https://`) when `$share` is on, so social networks can open it.
     *
     * @param string|null $tag [optional]
     * The category shown above the title (e.g., "Instruments"). It is shown in capitals.
     *
     * @param string|null $description [optional]
     * A short text under the title. It is cut off after three lines.
     *
     * @param string|null $image [optional]
     * The image URL. Without it, the image area shows the grey placeholder.
     *
     * @param string|null $imageAlt [optional]
     * The image's alt text. Defaults to empty, because the title already describes the image.
     *
     * @param string|null $readTime [optional]
     * The reading time, shown as written (e.g., "6 minutes read").
     *
     * @param bool $share [optional]
     * If set to `true`, adds share buttons for LinkedIn, X, Facebook and email. Requires `$url`.
     *
     * @param int $headingLevel [optional]
     * The heading level of the title: 3 (default), or 2 to 6 to fit the page outline.
     *
     * @throws InvalidArgumentException if `$title` is empty, `$headingLevel` is not accepted,
     * or `$share` is on without a `$url`.
     */
    public function __construct(
        $title,
        $url = null,
        $tag = null,
        $description = null,
        $image = null,
        $imageAlt = null,
        $readTime = null,
        $share = false,
        $headingLevel = 3
    ) {
        // Validate the arguments before building anything
        self::validate($title, $headingLevel);
        if ($share && !self::isFilled($url)) {
            throw new InvalidArgumentException("Sharing an article card needs its url: $title");
        }


        // Build the card: image, then the body with the tag, title and description
        $html = "<article class=\"article-card\">";
        $html .= self::renderImage($image, $imageAlt);
        $html .= "<div class=\"card-body\">";
        if (self::isFilled($tag)) {
            $html .= "<span class=\"card-tag\">" . self::escape($tag) . "</span>";
        }
        $html .= self::renderTitle($title, $headingLevel, $url);
        $html .= self::renderDescription($description);

        // Add the footer with the reading time and the share buttons, when there is something to show
        if (self::isFilled($readTime) || $share) {
            $html .= "<div class=\"card-footer\">";
            $html .= "<span class=\"card-read-time\">" . self::escape($readTime ?? '') . "</span>";
            if ($share) {
                $html .= self::renderShareButtons($url, $title);
            }
            $html .= "</div>";
        }
        $html .= "</div></article>";


        // Store and print the HTML
        $this->output($html);
    }



    /**
     * Builds the list of share buttons.
     *
     * @param string $url
     * The article URL to share.
     *
     * @param string $title
     * The article title, used as the message or email subject.
     *
     * @return string
     * The share buttons' HTML.
     */
    private static function renderShareButtons($url, $title) {
        $html = "<ul class=\"card-share\" aria-label=\"Share this article\">";
        foreach (self::SHARE_TARGETS as $label => [$pattern, $icon]) {
            $href = str_replace(['{url}', '{title}'], [rawurlencode($url), rawurlencode($title)], $pattern);
            $isEmail = $label === 'email';
            $html .= "<li><a" . self::attributesToHtml([
                'class' => 'card-share-button',
                'href' => $href,
                'aria-label' => $isEmail ? 'Share by email' : "Share on $label",
                'target' => $isEmail ? null : '_blank',
                'rel' => $isEmail ? null : 'noopener',
            ]) . ">" . self::renderIcon($icon) . "</a></li>";
        }
        return $html . "</ul>";
    }
}
