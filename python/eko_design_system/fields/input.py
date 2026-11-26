from typing import Any

from .field import Field


class Input(Field):
    """
    `Input` renders a single-line `<input>` field.
    It returns its HTML instead of printing it: use it as a string with `str(Input(...))`,
    or print it in a Jinja template with `{{ field }}`.
    Import it with `from eko_design_system.fields import Input`.

    Examples:
    - `Input("Full Name", name = "name", placeholder = "John Doe", required = True)`
    - `Input("E-mail", name = "email", type = "email", state = "error", info = "Please enter a valid e-mail address.")`
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
        type: str = "text",
        placeholder: str | None = None,
        value: str | None = None
    )-> None:
        """
        Build the input field and store the HTML in `html`.
        Pass keyword arguments so the optional parameters can be skipped.

        ### Parameters
        - label (`str`): The visible label text. " *" is added when `required` is `True`.
        - name (`str`, optional): The form field name. Also used as the id when `id` is not given.
        - id (`str`, optional): The element id, linked to the label and the info text. Defaults to `name`.
        - state (`str`, optional): "error", "success", "disabled", or `None` for the default state.
        - info (`str`, optional): Help or validation text shown under the input.
        - required (`bool`, optional): If set to `True`, adds the `required` attribute.
        - attributes (`dict[str, Any]`, optional): Extra attributes for the input as name: value
        (e.g., `{"autocomplete": "email"}`). `True` writes a bare attribute, `False` or `None` leaves it out.
        - type (`str`, optional): The HTML input type: "text" (default), "email", "tel", "number", etc.
        - placeholder (`str`, optional): The placeholder text.
        - value (`str`, optional): The initial value.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `state` is not an accepted value, or neither `id` nor `name` is given.
        """
        # Store the input's own arguments first: the base class renders the control while it is created
        self._type = type
        self._placeholder = placeholder
        self._value = value

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
        Build the `<input>` element.

        ### Returns
        - `str`: The input's HTML.
        """
        attributes = {
            "type": self._type,
            "placeholder": self._placeholder,
            "value": self._value,
            **self._base_attributes(),
        }
        return "<input" + self._attributes_to_html(attributes) + " />"
