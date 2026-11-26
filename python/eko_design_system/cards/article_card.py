from urllib.parse import quote

from .image_card import ImageCard


class ArticleCard(ImageCard):
    """
    `ArticleCard` renders a blog or news card (`.article-card`, see assets/css/components/image-card.css):
    a photo, a category tag, a title, a description, and a footer with the reading time and share buttons.
    It returns its HTML instead of printing it: use it as a string with `str(ArticleCard(...))`,
    or print it in a Jinja template with `{{ card }}`.
    Import it with `from eko_design_system.cards import ArticleCard`.

    The title and description are cut off after three lines.

    Example:
    ```python
    ArticleCard(
        title = "How to choose the right pyranometer",
        url = "https://www.eko-instruments.com/blog/choosing-a-pyranometer",
        tag = "Instruments",
        description = "A short introduction to the ISO 9060 classes and what they mean for your project.",
        image = "/assets/images/blog/pyranometer.jpg",
        read_time = "6 minutes read",
        share = True
    )
    ```

    All text and attribute values are HTML-escaped.
    """


    _SHARE_TARGETS: dict[str, tuple[str, str]] = {
        "LinkedIn": ("https://www.linkedin.com/sharing/share-offsite/?url={url}", "linkedin-alt-monochrome"),
        "X": ("https://twitter.com/intent/tweet?url={url}&text={title}", "twitter-monochrome"),
        "Facebook": ("https://www.facebook.com/sharer/sharer.php?u={url}", "facebook-f-monochrome"),
        "email": ("mailto:?subject={title}&body={url}", "envelope-outline"),
    }
    """
    The share buttons, in display order: label: (share URL pattern, icon).
    In the pattern, `{url}` is replaced by the article URL and `{title}` by the title, both URL-encoded.
    """


    def __init__(
        self,
        title: str,
        url: str | None = None,
        tag: str | None = None,
        description: str | None = None,
        image: str | None = None,
        image_alt: str | None = None,
        read_time: str | None = None,
        share: bool = False,
        heading_level: int = 3
    )-> None:
        """
        Build the article card HTML and store it in `html`.
        Pass keyword arguments so the optional parameters can be skipped.

        ### Parameters
        - title (`str`): The article title. It is cut off after three lines.
        - url (`str`, optional): The article URL. The title links to it, and the share buttons share it.
        Use a full URL (with `https://`) when `share` is on, so social networks can open it.
        - tag (`str`, optional): The category shown above the title (e.g., "Instruments"). It is shown in capitals.
        - description (`str`, optional): A short text under the title. It is cut off after three lines.
        - image (`str`, optional): The image URL. Without it, the image area shows the grey placeholder.
        - image_alt (`str`, optional): The image's alt text. Defaults to empty, because the title already describes the image.
        - read_time (`str`, optional): The reading time, shown as written (e.g., "6 minutes read").
        - share (`bool`, optional): If set to `True`, adds share buttons for LinkedIn, X, Facebook and email. Requires `url`.
        - heading_level (`int`, optional): The heading level of the title: 3 (default), or 2 to 6 to fit the page outline.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `title` is empty, `heading_level` is not accepted, or `share` is on without a `url`.
        """
        # Validate the arguments before building anything
        self._validate(title, heading_level)
        # Raises a ValueError on an empty title or an invalid heading level
        if share and not self._is_filled(url):
            raise ValueError(f"Sharing an article card needs its url: {title}")


        # Build the card: image, then the body with the tag, title and description
        html = "<article class=\"article-card\">"
        html += self._render_image(image, image_alt)
        html += "<div class=\"card-body\">"
        if self._is_filled(tag):
            html += f"<span class=\"card-tag\">{self._escape(tag)}</span>"
        html += self._render_title(title, heading_level, url)
        html += self._render_description(description)

        # Add the footer with the reading time and the share buttons, when there is something to show
        if self._is_filled(read_time) or share:
            html += "<div class=\"card-footer\">"
            html += f"<span class=\"card-read-time\">{self._escape(read_time if read_time is not None else '')}</span>"
            if share:
                html += self._render_share_buttons(url, title)
            html += "</div>"
        html += "</div></article>"


        # Store the HTML
        self.html = html


    @staticmethod
    def _render_share_buttons(
        url: str,
        title: str
    )-> str:
        """
        Build the list of share buttons.

        The URL and title are encoded with `quote(value, safe = "")`, which encodes the same characters as PHP's `rawurlencode()`.

        ### Parameters
        - url (`str`): The article URL to share.
        - title (`str`): The article title, used as the message or email subject.

        ### Returns
        - `str`: The share buttons' HTML.
        """
        encoded_url = quote(ArticleCard._to_string(url), safe = "")
        encoded_title = quote(ArticleCard._to_string(title), safe = "")

        html = "<ul class=\"card-share\" aria-label=\"Share this article\">"
        for label, (pattern, icon) in ArticleCard._SHARE_TARGETS.items():
            href = pattern.replace("{url}", encoded_url).replace("{title}", encoded_title)
            is_email = label == "email"
            html += "<li><a" + ArticleCard._attributes_to_html({
                "class": "card-share-button",
                "href": href,
                "aria-label": "Share by email" if is_email else f"Share on {label}",
                "target": None if is_email else "_blank",
                "rel": None if is_email else "noopener",
            }) + ">" + ArticleCard._render_icon(icon) + "</a></li>"
        return html + "</ul>"
