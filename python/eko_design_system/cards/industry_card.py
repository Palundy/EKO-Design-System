from .image_card import ImageCard


class IndustryCard(ImageCard):
    """
    `IndustryCard` renders a wide industry card (`.industry-card`, see css/components/image-card.css):
    a photo, a large title and a blue description.
    It returns its HTML instead of printing it: use it as a string with `str(IndustryCard(...))`,
    or print it in a Jinja template with `{{ card }}`.
    Import it with `from eko_design_system.cards import IndustryCard`.

    With a `url`, the whole card is a link (`<a class="industry-card">`), otherwise it is an `<article>`.

    Example:
    ```python
    IndustryCard(
        title = "Solar Energy",
        description = "Precision instruments for solar resource assessment and PV performance monitoring.",
        image = "/assets/images/solar-energy.jpg",
        url = "/industries/solar-energy"
    )
    ```

    All text and attribute values are HTML-escaped.
    """


    def __init__(
        self,
        title: str,
        description: str | None = None,
        image: str | None = None,
        image_alt: str | None = None,
        url: str | None = None,
        heading_level: int = 3
    )-> None:
        """
        Build the industry card HTML and store it in `html`.
        Pass keyword arguments so the optional parameters can be skipped.

        ### Parameters
        - title (`str`): The industry name.
        - description (`str`, optional): A short text under the title.
        - image (`str`, optional): The image URL. Without it, the image area shows the grey placeholder.
        - image_alt (`str`, optional): The image's alt text. Defaults to empty, because the title already describes the image.
        - url (`str`, optional): Where the card links to. With a URL the whole card is clickable.
        - heading_level (`int`, optional): The heading level of the title: 3 (default), or 2 to 6 to fit the page outline.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `title` is empty or `heading_level` is not accepted.
        """
        # Validate the arguments before building anything
        self._validate(title, heading_level)
        # Raises a ValueError on an empty title or an invalid heading level


        # Build the card as a link when there is a URL, otherwise as an article
        if self._is_filled(url):
            open_tag = f"<a class=\"industry-card\" href=\"{self._escape(url)}\">"
            close_tag = "</a>"
        else:
            open_tag = "<article class=\"industry-card\">"
            close_tag = "</article>"

        html = open_tag
        html += self._render_image(image, image_alt)
        html += "<div class=\"card-body\">"
        html += self._render_title(title, heading_level)
        html += self._render_description(description)
        html += "</div>" + close_tag


        # Store the HTML
        self.html = html
