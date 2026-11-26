from .image_card import ImageCard


class ApplicationCard(ImageCard):
    """
    `ApplicationCard` renders an application card (`.application-card`, see css/components/image-card.css):
    a photo, a title, a description and a "Learn More →" link.
    It returns its HTML instead of printing it: use it as a string with `str(ApplicationCard(...))`,
    or print it in a Jinja template with `{{ card }}`.
    Import it with `from eko_design_system.cards import ApplicationCard`.

    Example:
    ```python
    ApplicationCard(
        title = "Solar Energy",
        description = "Resource assessment, performance monitoring and plant optimization.",
        image = "/assets/images/solar-energy.jpg",
        cta = "Learn More",
        cta_url = "/applications/solar-energy"
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
        cta: str | None = None,
        cta_url: str | None = None,
        heading_level: int = 3
    )-> None:
        """
        Build the application card HTML and store it in `html`.
        Pass keyword arguments so the optional parameters can be skipped.

        ### Parameters
        - title (`str`): The application name.
        - description (`str`, optional): A short text under the title.
        - image (`str`, optional): The image URL. Without it, the image area shows the grey placeholder.
        - image_alt (`str`, optional): The image's alt text. Defaults to empty, because the title already describes the image.
        - cta (`str`, optional): The text of the link at the bottom of the card (e.g., "Learn More"). An arrow is added after it.
        - cta_url (`str`, optional): Where the link goes. Required when `cta` is given.
        - heading_level (`int`, optional): The heading level of the title: 3 (default), or 2 to 6 to fit the page outline.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `title` is empty, `heading_level` is not accepted, or `cta` is given without `cta_url`.
        """
        # Validate the arguments before building anything
        self._validate(title, heading_level)
        # Raises a ValueError on an empty title or an invalid heading level
        if self._is_filled(cta) and not self._is_filled(cta_url):
            raise ValueError(f"The application card link needs a ctaUrl: {cta}")


        # Build the card: image, then the body with the title, description and link
        html = "<article class=\"application-card\">"
        html += self._render_image(image, image_alt)
        html += "<div class=\"card-body\">"
        html += self._render_title(title, heading_level)
        html += self._render_description(description)
        if self._is_filled(cta):
            html += (
                f"<a class=\"card-cta link link-action\" href=\"{self._escape(cta_url)}\">"
                + self._escape(cta) + " <span aria-hidden=\"true\">→</span></a>"
            )
        html += "</div></article>"


        # Store the HTML
        self.html = html
