from typing import Any

from eko_design_system.component import Component


class SectionHeading(Component):
    """
    `SectionHeading` renders the title above a page section, with an optional action link on the right
    (`.section-heading`, see assets/css/components/section-heading.css).
    It returns its HTML instead of printing it: use it as a string with `str(SectionHeading(...))`,
    or print it in a Jinja template with `{{ heading }}`.
    Import it with `from eko_design_system import SectionHeading`.

    Examples:
    - `SectionHeading("Popular Products")`
    - `SectionHeading("Popular Products", link = "View all", link_url = "/products")`

    All text and attribute values are HTML-escaped.
    The output is identical to the PHP `EKO\\DesignSystem\\SectionHeading`.
    """


    _VALID_HEADING_LEVELS: tuple[int, ...] = (1, 2, 3, 4, 5, 6)
    """Heading levels accepted by the `heading_level` argument (`<h1>` to `<h6>`)."""


    def __init__(
        self,
        title: str,
        link: str | None = None,
        link_url: str | None = None,
        heading_level: int = 2
    )-> None:
        """
        Build the section heading HTML and store it in `html`.
        Pass keyword arguments so the optional parameters can be skipped.

        ### Parameters
        - title (`str`): The section title.
        - link (`str`, optional): The text of the action link on the right (e.g., "View all"). An arrow is added after it.
        - link_url (`str`, optional): Where the link goes. Required when `link` is given.
        - heading_level (`int`, optional): The heading element of the title: 2 (default), or 1 to 6 to fit the page outline.
        The look stays the same. It must be an `int`, like PHP's strict check.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `title` is empty, `heading_level` is not accepted, or `link` is given without `link_url`.
        """
        # Validate the arguments before building anything
        if title is None or title == "":
            raise ValueError("A section heading needs a title")
        if type(heading_level) is not int or heading_level not in self._VALID_HEADING_LEVELS:
            raise ValueError(f"Invalid section heading level: {self._to_string(heading_level)}")
        has_link = link is not None and link != ""
        if has_link and (link_url is None or link_url == ""):
            raise ValueError(f"The section heading link needs a linkUrl: {link}")


        # Build the title, followed by the optional link
        html = "<div class=\"section-heading\">"
        html += f"<h{heading_level} class=\"section-heading-title\">{self._escape(title)}</h{heading_level}>"
        if has_link:
            html += (
                f"<a class=\"link link-action\" href=\"{self._escape(link_url)}\">"
                + self._escape(link) + " <span aria-hidden=\"true\">→</span></a>"
            )
        html += "</div>"


        # Store the HTML
        self.html = html


    @staticmethod
    def _escape(value: Any)-> str:
        """
        Escape a value for use in HTML text or a quoted attribute, like PHP's `htmlspecialchars($value, ENT_QUOTES)`.

        ### Parameters
        - value (`Any`): The value to escape. It is converted to a string first, like PHP does (see `_to_string()`).

        ### Returns
        - `str`: The escaped value.
        """
        return (
            SectionHeading._to_string(value)
            .replace("&", "&amp;")
            .replace("<", "&lt;")
            .replace(">", "&gt;")
            .replace("\"", "&quot;")
            .replace("'", "&#039;")
        )


    @staticmethod
    def _to_string(value: Any)-> str:
        """
        Convert a value to a string the way PHP's `(string)` cast does, so the output matches the PHP component.

        ### Parameters
        - value (`Any`): The value to convert.

        ### Returns
        - `str`: "" for `None` and `False`, "1" for `True`, "2" for a whole float such as `2.0`,
        and `str(value)` for anything else.
        """
        if value is None or value is False:
            return ""
        if value is True:
            return "1"
        if isinstance(value, float) and value.is_integer() and abs(value) < 1e15:
            return str(int(value))
        return str(value)
