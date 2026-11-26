from typing import Any

from .field import Field


class Dropdown(Field):
    """
    `Dropdown` renders a dropdown field: a native `<select>` inside `.dropdown`,
    which js/dropdowns.js turns into the styled dropdown. Include that script on the page.
    It returns its HTML instead of printing it: use it as a string with `str(Dropdown(...))`,
    or print it in a Jinja template with `{{ field }}`.
    Import it with `from eko_design_system.fields import Dropdown`.

    Example:
    - `Dropdown("Product of interest", name = "product", options = {"ms-80sh": "MS-80SH", "ms-80": "MS-80"}, value = "ms-80", required = True)`

    The native `<select>` stays the source of truth: it holds the value and takes part in form submission and validation.
    """


    def __init__(
        self,
        label: str,
        name: str | None = None,
        id: str | None = None,
        state: str | None = None,
        info: str | None = None,
        required: bool = False,
        attributes: dict[str, Any] | None = None,
        options: dict[Any, str] | None = None,
        value: Any = None,
        placeholder: str | None = "Select"
    )-> None:
        """
        Build the dropdown field and store the HTML in `html`.
        Pass keyword arguments so the optional parameters can be skipped.

        ### Parameters
        - label (`str`): The visible label text. " *" is added when `required` is `True`.
        - name (`str`, optional): The form field name. Also used as the id when `id` is not given.
        - id (`str`, optional): The element id, linked to the label and the info text. Defaults to `name`.
        - state (`str`, optional): "error", "success", "disabled", or `None` for the default state.
        - info (`str`, optional): Help or validation text shown under the dropdown.
        - required (`bool`, optional): If set to `True`, adds the `required` attribute.
        - attributes (`dict[str, Any]`, optional): Extra attributes for the `<select>` as name: value.
        `True` writes a bare attribute, `False` or `None` leaves it out.
        - options (`dict[Any, str]`, optional): The choices as value: visible text (e.g., `{"ms-80": "MS-80"}`), in display order.
        - value (`Any`, optional): The value of the option that starts selected. It is compared as a string, so `5` matches `"5"`.
        - placeholder (`str`, optional): The text of the empty first option, which cannot be picked. Defaults to "Select".
        It starts selected when `value` is `None`. Pass `None` to leave it out.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `state` is not an accepted value, or neither `id` nor `name` is given.
        """
        # Store the dropdown's own arguments first: the base class renders the control while it is created
        self._options = options or {}
        self._value = value
        self._placeholder = placeholder

        super().__init__(
            label = label,
            name = name,
            id = id,
            state = state,
            info = info,
            required = required,
            attributes = attributes
        )
        # Raises a ValueError on an invalid state or a missing id and name


    def _render_control(self)-> str:
        """
        Build the `.dropdown` wrapper with the `<select>` and its options.

        ### Returns
        - `str`: The dropdown's HTML.
        """
        html = "<div class=\"dropdown\">"
        html += "<select" + self._attributes_to_html(self._base_attributes()) + ">"

        # Add the placeholder option, selected when no value is chosen
        if self._placeholder is not None:
            html += "<option" + self._attributes_to_html({
                "value": "",
                "disabled": True,
                "selected": self._value is None,
            }) + ">" + self._escape(self._placeholder) + "</option>"

        # Add the options, marking the one that matches `value`; both sides are compared as strings, like in PHP
        for option_value, text in self._options.items():
            html += "<option" + self._attributes_to_html({
                "value": option_value,
                "selected": self._value is not None and self._to_string(option_value) == self._to_string(self._value),
            }) + ">" + self._escape(text) + "</option>"

        html += "</select></div>"
        return html
