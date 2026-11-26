from typing import Any

from .card import Card


class ImageCard(Card):
    """
    `ImageCard` is the shared base of the image cards `ApplicationCard`, `IndustryCard` and `ArticleCard`
    (see css/components/image-card.css). It is not used directly.

    It builds the parts these cards have in common: the photo across the top, the title and the description.
    Every image card has the same outline:
    ```html
    <article class="{card}">
        <div class="card-image"><img src="{image}" alt="{image_alt}"></div>
        <div class="card-body">
            <h3 class="card-title">{title}</h3>
            <p class="card-description">{description}</p>
            …
        </div>
    </article>
    ```
    """


    VALID_HEADING_LEVELS: tuple[int, ...] = (2, 3, 4, 5, 6)
    """Heading levels accepted for the card title (`<h2>` to `<h6>`)."""


    @staticmethod
    def _validate(
        title: str,
        heading_level: int
    )-> None:
        """
        Check the arguments every image card shares.

        The heading level must be an `int`: like PHP's strict `in_array()`, "3" or 3.0 are not accepted.

        ### Parameters
        - title (`str`): The card title, which may not be empty.
        - heading_level (`int`): The heading level of the title, one of `VALID_HEADING_LEVELS`.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `title` is empty or `heading_level` is not accepted.
        """
        if not ImageCard._is_filled(title):
            raise ValueError("An image card needs a title")
        if type(heading_level) is not int or heading_level not in ImageCard.VALID_HEADING_LEVELS:
            raise ValueError(f"Invalid image card heading level: {ImageCard._to_string(heading_level)}")


    @staticmethod
    def _render_image(
        image: str | None,
        image_alt: str | None
    )-> str:
        """
        Build the image area. Without an image, the area shows the grey placeholder.

        ### Parameters
        - image (`str | None`): The image URL, or `None` for the placeholder.
        - image_alt (`str | None`): The alt text. Defaults to an empty string,
        because the title next to the image already says what it shows.

        ### Returns
        - `str`: The image area's HTML.
        """
        html = "<div class=\"card-image\">"
        if ImageCard._is_filled(image):
            html += "<img" + ImageCard._attributes_to_html({
                "src": image,
                "alt": image_alt if image_alt is not None else "",
                "loading": "lazy",
            }) + ">"
        return html + "</div>"


    @staticmethod
    def _render_title(
        title: str,
        heading_level: int,
        url: str | None = None
    )-> str:
        """
        Build the title, optionally as a link.

        ### Parameters
        - title (`str`): The title text.
        - heading_level (`int`): The heading level, 2 to 6.
        - url (`str`, optional): Where the title links to, or `None` for plain text.

        ### Returns
        - `str`: The title's HTML.
        """
        text = ImageCard._escape(title)
        if ImageCard._is_filled(url):
            text = f"<a href=\"{ImageCard._escape(url)}\">{text}</a>"
        return f"<h{heading_level} class=\"card-title\">{text}</h{heading_level}>"


    @staticmethod
    def _render_description(description: Any)-> str:
        """
        Build the description, or nothing when there is none.

        ### Parameters
        - description (`Any`): The description text.

        ### Returns
        - `str`: The description's HTML, or an empty string.
        """
        if not ImageCard._is_filled(description):
            return ""
        return f"<p class=\"card-description\">{ImageCard._escape(description)}</p>"
