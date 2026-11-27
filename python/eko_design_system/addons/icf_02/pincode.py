from typing import Any

from eko_design_system.component import Component
from eko_design_system.design_system import DesignSystem


class Pincode(Component):
    """
    `Pincode` renders the ICF-02 sign-in card: a padlock, a dot per digit, a message and a numeric keypad
    (`.icf02-pincode`, see assets/addons/icf-02/css/components/pincode.css). Part of the ICF-02 addon,
    so enable it first with `DesignSystem.enable_addon(Icf02)`, or pass `addons = [Icf02]` to the Flask extension;
    the scripts helper then also loads assets/addons/icf-02/js/pincode.js, which handles the keypad,
    the keyboard's number keys and the animations.
    It returns its HTML instead of printing it: use it as a string with `str(Pincode(...))`,
    or print it in a Jinja template with `{{ pincode }}`.
    Import it with `from eko_design_system.addons.icf_02 import Pincode`.

    Example:
    ```python
    Pincode(
        title = "ICF-02 · Unit 005",
        logo = "/static/images/eko-logo.svg",
        link = "Change pincode",
        attributes = {"id": "login"}
    )
    ```
    The app checks the pincode: listen for the `icf02-pincode-submit` event and call `event.detail.unlock()`
    or `event.detail.reject()`. See the script's header comment.

    All text and attribute values are HTML-escaped.
    The output is identical to the PHP `EKO\\DesignSystem\\Addons\\Icf02\\Pincode`.
    """


    _VALID_STATES: tuple[str, ...] = (
        "locked",
        "error",
        "unlocked",
    )
    """
    States accepted by the `state` argument:
    - "locked": waiting for the pincode. The default.
    - "error": the pincode was wrong. Red lock and dots, and the error message.
    - "unlocked": the pincode was right. An open, green lock and dots, the unlocked message and a disabled keypad.
    """

    _VALID_LENGTHS: tuple[int, ...] = (4, 5, 6, 7, 8)
    """Pincode lengths accepted by the `length` argument."""

    _LOCK_SVG: str = (
        "<svg viewBox=\"0 0 48 48\" focusable=\"false\">"
        + "<path class=\"icf02-pincode-shackle\" d=\"M16 22v-7a8 8 0 0 1 16 0v7\"/>"
        + "<rect class=\"icf02-pincode-body\" x=\"10\" y=\"21\" width=\"28\" height=\"21\" rx=\"5\"/>"
        + "<path class=\"icf02-pincode-keyhole\" d=\"M24 27.5a3 3 0 0 1 1.5 5.6V36h-3v-2.9a3 3 0 0 1 1.5-5.6z\"/>"
        + "</svg>"
    )
    """The padlock: a shackle that swings open, the body and its keyhole."""

    _DELETE_ICON: str = "backspace-outline"
    """The icon on the delete key."""


    def __init__(
        self,
        title: str = "ICF-02",
        logo: str | None = None,
        logo_alt: str = "EKO",
        length: int = 4,
        message: str | None = None,
        state: str = "locked",
        error_message: str = "Wrong pincode. Try again.",
        unlocked_message: str = "Unlocked",
        submit_label: str = "Sign in",
        delete_label: str = "Delete",
        link: str | None = None,
        link_url: str | None = None,
        label: str = "Sign in",
        attributes: dict[str, Any] | None = None
    )-> None:
        """
        Build the pincode card HTML and store it in `html`.
        Pass keyword arguments so the optional parameters can be skipped.

        ### Parameters
        - title (`str`, optional): The bold line at the top (e.g., "ICF-02 · Unit 005"), "ICF-02" by default.
        - logo (`str`, optional): The URL of a logo image above the title. It is used as given.
        - logo_alt (`str`, optional): The logo's alternative text, "EKO" by default.
        - length (`int`, optional): The number of digits, 4 to 8. 4 by default. It must be an `int`, like PHP's strict check.
        - message (`str`, optional): The message while waiting for the pincode.
        By default "Enter your 4-digit pincode", with the length filled in.
        - state (`str`, optional): One of `_VALID_STATES`: "locked" (default), "error" or "unlocked".
        The script changes it as the pincode is checked.
        - error_message (`str`, optional): The message after a wrong pincode, "Wrong pincode. Try again." by default.
        - unlocked_message (`str`, optional): The message once unlocked, "Unlocked" by default.
        - submit_label (`str`, optional): The text of the submit key, "Sign in" by default.
        - delete_label (`str`, optional): The name of the delete key for screen readers and as a tooltip, "Delete" by default.
        - link (`str`, optional): The text of a link under the keypad (e.g., "Change pincode").
        - link_url (`str`, optional): Where the link goes. Without it, the link is a `<button type="button">`
        for the app's own script.
        - label (`str`, optional): The accessible name of the card, "Sign in" by default.
        - attributes (`dict[str, Any]`, optional): Extra HTML attributes for the card's `<div>` as name: value
        (e.g., `{"id": "login"}`). A "class" is added to the component's own classes.
        `True` writes a bare attribute, `False` or `None` leaves it out.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `title` is empty, `length` or `state` is not accepted, or `link_url` is given without `link`.
        """
        # Validate the arguments before building anything
        if title is None or title == "":
            raise ValueError("A pincode needs a title")
        if type(length) is not int or length not in self._VALID_LENGTHS:
            raise ValueError(f"Invalid pincode length: {self._to_string(length)}")
        if not isinstance(state, str) or state not in self._VALID_STATES:
            raise ValueError(f"Invalid pincode state: {self._to_string(state)}")
        has_link = link is not None and link != ""
        if not has_link and link_url is not None and link_url != "":
            raise ValueError("A pincode linkUrl needs a link text")
        if message is None or message == "":
            message = f"Enter your {length}-digit pincode"


        # The message and the dots follow the state: a checked pincode shows all its dots
        messages = {"locked": message, "error": error_message, "unlocked": unlocked_message}
        filled = 0 if state == "locked" else length

        # Collect the card's attributes, adding any extra class to the component's own
        all_attributes: dict[str, Any] = {
            "class": "icf02-pincode" + ("" if state == "locked" else f" {state}"),
            "role": "group",
            "aria-label": label,
            "data-length": length,
            "data-message": message,
            "data-error-message": error_message,
            "data-unlocked-message": unlocked_message,
        }
        for key, value in (attributes or {}).items():
            if key == "class":
                all_attributes["class"] += " " + self._to_string(value)
            else:
                all_attributes[key] = value

        # Build the head: the optional logo, the title and the padlock
        html = "<div" + self._attributes_to_html(all_attributes) + ">"
        if logo is not None and logo != "":
            html += f"<img class=\"icf02-pincode-logo\" src=\"{self._escape(logo)}\" alt=\"{self._escape(logo_alt)}\">"
        html += f"<span class=\"icf02-pincode-title\">{self._escape(title)}</span>"
        html += f"<span class=\"icf02-pincode-lock\" aria-hidden=\"true\">{self._LOCK_SVG}</span>"

        # Build the dots, each with its index so their animations can follow one another
        html += f"<div class=\"icf02-pincode-dots\" role=\"img\" aria-label=\"{filled} of {length} digits entered\">"
        for index in range(length):
            html += (
                "<span class=\"icf02-pincode-dot" + (" filled" if index < filled else "") + "\""
                + f" style=\"--icf02-pincode-dot-index: {index}\"></span>"
            )
        html += "</div>"
        html += f"<p class=\"icf02-pincode-message\" role=\"status\">{self._escape(messages[state])}</p>"

        # Build the keypad: 1 to 9, then delete, 0 and submit. An unlocked card takes no more input
        disabled = " disabled" if state == "unlocked" else ""
        html += "<div class=\"icf02-pincode-keys\">"
        for digit in ("1", "2", "3", "4", "5", "6", "7", "8", "9"):
            html += f"<button class=\"icf02-pincode-key\" type=\"button\" data-key=\"{digit}\"{disabled}>{digit}</button>"
        delete_icon = "--icon-url: url('" + self._escape_css_string(DesignSystem.resolve_icon(self._DELETE_ICON)) + "')"
        html += (
            "<button class=\"icf02-pincode-key icf02-pincode-key-delete\" type=\"button\" data-key=\"delete\""
            + f" aria-label=\"{self._escape(delete_label)}\" title=\"{self._escape(delete_label)}\"{disabled}>"
            + f"<span class=\"icon\" style=\"{self._escape(delete_icon)}\" aria-hidden=\"true\"></span></button>"
        )
        html += f"<button class=\"icf02-pincode-key\" type=\"button\" data-key=\"0\"{disabled}>0</button>"
        html += (
            f"<button class=\"icf02-pincode-key icf02-pincode-key-submit\" type=\"button\" data-key=\"enter\"{disabled}>"
            + self._escape(submit_label) + "</button>"
        )
        html += "</div>"

        # Build the optional link under the keypad
        if has_link:
            if link_url is not None and link_url != "":
                html += f"<a class=\"link icf02-pincode-link\" href=\"{self._escape(link_url)}\">{self._escape(link)}</a>"
            else:
                html += f"<button class=\"link icf02-pincode-link\" type=\"button\">{self._escape(link)}</button>"
        html += "</div>"


        # Store the HTML
        self.html = html


    @staticmethod
    def _attributes_to_html(attributes: dict[str, Any])-> str:
        """
        Turn name: value pairs into an escaped attribute string with a leading space.

        ### Parameters
        - attributes (`dict[str, Any]`): The attributes as name: value.
        `True` writes a bare attribute (e.g., ` hidden`), `False` and `None` are skipped.

        ### Returns
        - `str`: The attributes as HTML (e.g., ` class="icf02-pincode" id="login"`).
        """
        html = ""
        for key, value in attributes.items():
            if value is None or value is False:
                continue
            html += " " + Pincode._escape(key)
            if value is not True:
                html += f"=\"{Pincode._escape(value)}\""
        return html


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
            Pincode._to_string(value)
            .replace("&", "&amp;")
            .replace("<", "&lt;")
            .replace(">", "&gt;")
            .replace("\"", "&quot;")
            .replace("'", "&#039;")
        )


    @staticmethod
    def _escape_css_string(value: Any)-> str:
        """
        Escape a value for use inside a single-quoted CSS string, such as `url('...')`.

        Quotes, backslashes and line breaks are replaced by CSS escapes so the value cannot end the string.
        The result still needs `_escape()` when it is placed in an HTML attribute.

        ### Parameters
        - value (`Any`): The value to escape. It is converted to a string first.

        ### Returns
        - `str`: The escaped value.
        """
        # The backslash goes first, so the backslashes of the other escapes are not escaped again
        return (
            Pincode._to_string(value)
            .replace("\\", "\\5c ")
            .replace("'", "\\27 ")
            .replace("\"", "\\22 ")
            .replace("\n", "\\a ")
            .replace("\r", "\\d ")
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
