from typing import Any

from eko_design_system.component import Component
from eko_design_system.design_system import DesignSystem


class Sidebar(Component):
    """
    `Sidebar` renders the ICF-02 application's main menu: a full-height column with the logo and product name,
    the menu links in labelled groups, and an optional footer with the unit's details and icon actions
    (`.icf02-sidebar`, see assets/addons/icf-02/css/components/sidebar.css). Part of the ICF-02 addon,
    so enable it first with `DesignSystem.enable_addon(Icf02)`, or pass `addons = [Icf02]` to the Flask extension.
    It returns its HTML instead of printing it: use it as a string with `str(Sidebar(...))`,
    or print it in a Jinja template with `{{ sidebar }}`.
    Import it with `from eko_design_system.addons.icf_02 import Sidebar`.

    Example:
    ```python
    Sidebar(
        logo = "/static/images/eko-logo.svg",
        groups = {
            "Work": [
                {"label": "Home", "url": "/", "icon": "house-outline", "active": True},
                {"label": "Calibrate", "url": "/calibrate", "icon": "target-outline"},
            ],
            "System": [
                {"label": "Settings", "url": "/settings", "icon": "setting-outline"},
            ],
        },
        footer_title = "Unit 005",
        footer_detail = "v5.6.0",
        footer_status = "Developer Mode",
        actions = [
            {"label": "Send feedback", "icon": "feedback-outline", "attributes": {"onclick": "showFeedbackElement()"}},
            {"label": "Shut down", "icon": "power-button-outline", "url": "/power"},
        ]
    )
    ```

    The page layout places the sidebar: it sticks to the top of the viewport and is as tall as it.
    All text and attribute values are HTML-escaped.
    The output is identical to the PHP `EKO\\DesignSystem\\Addons\\Icf02\\Sidebar`.
    """


    _VALID_ITEM_KEYS: tuple[str, ...] = (
        "label",
        "url",
        "icon",
        "active",
    )
    """The keys a menu item accepts."""

    _VALID_ACTION_KEYS: tuple[str, ...] = (
        "label",
        "icon",
        "url",
        "attributes",
    )
    """The keys a footer action accepts."""


    def __init__(
        self,
        groups: dict[Any, list[dict[str, Any]]] | list[list[dict[str, Any]]],
        logo: str,
        title: str = "ICF-02",
        logo_alt: str = "EKO",
        url: str = "/",
        footer_title: str | None = None,
        footer_detail: str | None = None,
        footer_status: str | None = None,
        actions: list[dict[str, Any]] | None = None,
        label: str = "Main",
        attributes: dict[str, Any] | None = None
    )-> None:
        """
        Build the sidebar HTML and store it in `html`.
        Pass keyword arguments so the optional parameters can be skipped.

        ### Parameters
        - groups (`dict[Any, list[dict[str, Any]]] | list[list[dict[str, Any]]]`): The menu, as group label: list of items.
        A group with an `int` key, or any group of a plain list, gets no label. Each item is a dict with the keys
        "label" (required: the link text), "url" (required: where the link goes), "icon" (optional: a bundled icon's name
        or the URL of your own SVG icon, see `DesignSystem.resolve_icon()`) and "active" (optional: `True` for the link
        of the current page, which gets `aria-current="page"`).
        - logo (`str`): The URL of the logo image at the top (e.g., "/static/images/eko-logo.svg"). It is used as given.
        - title (`str`, optional): The product name next to the logo, "ICF-02" by default.
        - logo_alt (`str`, optional): The logo's alternative text, "EKO" by default.
        - url (`str`, optional): Where the logo and product name link to, "/" by default.
        - footer_title (`str`, optional): The first, bold line of the footer (e.g., "Unit 005").
        - footer_detail (`str`, optional): The second, small line of the footer (e.g., "v5.6.0").
        - footer_status (`str`, optional): The last line of the footer: a state of the system, shown with a little emphasis
        (e.g., "Developer Mode", "Team Mode", "Outdated", "New Update Available").
        - actions (`list[dict[str, Any]]`, optional): Icon buttons on the right of the footer, each a dict with the keys
        "label" (required: what the action does, read by screen readers and shown as a tooltip), "icon" (required),
        "url" (optional: makes the action a link, otherwise it is a `<button type="button">`) and
        "attributes" (optional: extra HTML attributes as name: value, e.g. `{"onclick": "showFeedbackElement()"}`).
        - label (`str`, optional): The accessible name of the menu's `<nav>`, "Main" by default.
        - attributes (`dict[str, Any]`, optional): Extra HTML attributes for the `<aside>` as name: value
        (e.g., `{"id": "sidebar"}`). A "class" is added to the component's own class.
        `True` writes a bare attribute, `False` or `None` leaves it out.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `groups` is empty, a group has no items, an item has no label or url or an unknown key,
        `logo` or `title` is empty, an action has no label or icon or an unknown key, or an icon names an unknown icon.
        """
        actions = [] if actions is None else actions

        # Validate the arguments before building anything
        if not isinstance(groups, (dict, list, tuple)) or len(groups) == 0:
            raise ValueError("A sidebar needs at least one group")
        if logo is None or logo == "":
            raise ValueError("A sidebar needs a logo")
        if title is None or title == "":
            raise ValueError("A sidebar needs a title")
        if not isinstance(actions, (list, tuple)):
            raise ValueError("The sidebar actions must be a list")


        # Collect the aside's attributes, adding any extra class to the component's own
        all_attributes: dict[str, Any] = {"class": "icf02-sidebar"}
        for key, value in (attributes or {}).items():
            if key == "class":
                all_attributes["class"] += " " + self._to_string(value)
            else:
                all_attributes[key] = value

        # Build the brand: the logo and the product name, linking to the home page
        html = "<aside" + self._attributes_to_html(all_attributes) + ">"
        html += f"<a class=\"icf02-sidebar-brand\" href=\"{self._escape(url)}\">"
        html += f"<img class=\"icf02-sidebar-logo\" src=\"{self._escape(logo)}\" alt=\"{self._escape(logo_alt)}\">"
        html += f"<span class=\"icf02-sidebar-title\">{self._escape(title)}</span>"
        html += "</a>"

        # Build the menu, one group at a time. A list is read like PHP's foreach: its index is the key
        html += f"<nav class=\"icf02-sidebar-nav\" aria-label=\"{self._escape(label)}\">"
        entries = groups.items() if isinstance(groups, dict) else enumerate(groups)
        for group_label, items in entries:
            html += self._render_group(
                group_label = None if isinstance(group_label, int) else group_label,
                items = items
            )
            # Raises a ValueError on an invalid group or item
        html += "</nav>"

        # Build the footer, only when it has something to show
        has_meta = (footer_title is not None and footer_title != "") or (footer_detail is not None and footer_detail != "")
        has_status = footer_status is not None and footer_status != ""
        if has_meta or len(actions) > 0 or has_status:
            html += "<div class=\"icf02-sidebar-footer\">"
            if has_meta:
                html += "<div class=\"icf02-sidebar-meta\">"
                if footer_title is not None and footer_title != "":
                    html += f"<span class=\"icf02-sidebar-meta-title\">{self._escape(footer_title)}</span>"
                if footer_detail is not None and footer_detail != "":
                    html += f"<span class=\"icf02-sidebar-meta-detail\">{self._escape(footer_detail)}</span>"
                html += "</div>"
            if len(actions) > 0:
                html += "<div class=\"icf02-sidebar-actions\">"
                for action in actions:
                    html += self._render_action(action)
                    # Raises a ValueError on an invalid action
                html += "</div>"
            # The system state goes last: it gets a full-width row, so a long state fits on one line
            if has_status:
                html += f"<span class=\"icf02-sidebar-status\">{self._escape(footer_status)}</span>"
            html += "</div>"
        html += "</aside>"


        # Store the HTML
        self.html = html


    @staticmethod
    def _render_group(
        group_label: Any,
        items: Any
    )-> str:
        """
        Build the HTML of one group: its optional label and its list of links.

        The label is shown, but hidden from screen readers, because it also names the list (`aria-label`).

        ### Parameters
        - group_label (`Any`): The group's label, or `None` (or "") for a group without one.
        - items (`Any`): The group's items, as described for the `groups` argument of `__init__()`.

        ### Returns
        - `str`: The group's `<div>`.

        ### Raises
        - `ValueError`: If the group has no items or one of its items is invalid.
        """
        if not isinstance(items, (list, tuple)) or len(items) == 0:
            raise ValueError("Every sidebar group needs at least one item")
        has_label = group_label is not None and group_label != ""

        html = "<div class=\"icf02-sidebar-group\">"
        if has_label:
            html += f"<span class=\"icf02-sidebar-group-label\" aria-hidden=\"true\">{Sidebar._escape(group_label)}</span>"
        list_label = f" aria-label=\"{Sidebar._escape(group_label)}\"" if has_label else ""
        html += f"<ul class=\"icf02-sidebar-list\"{list_label}>"
        for item in items:
            html += Sidebar._render_item(item)
            # Raises a ValueError on an invalid item
        html += "</ul>"
        html += "</div>"
        return html


    @staticmethod
    def _render_item(item: Any)-> str:
        """
        Build the HTML of one menu item: a link with an optional icon, marked as the current page when it is active.

        ### Parameters
        - item (`Any`): The item, as described for the `groups` argument of `__init__()`.

        ### Returns
        - `str`: The item's `<li>`.

        ### Raises
        - `ValueError`: If the item is not a dict, has no label or url, has an unknown key or names an unknown icon.
        """
        # Validate the item
        item_label = item.get("label") if isinstance(item, dict) else None
        if item_label is None or item_label == "":
            raise ValueError("Every sidebar item needs a label")
        item_url = item.get("url")
        if item_url is None or item_url == "":
            raise ValueError("Every sidebar item needs a url")
        for key in item:
            if key not in Sidebar._VALID_ITEM_KEYS:
                raise ValueError(f"Unknown sidebar item key: {Sidebar._to_string(key)}")
        # Only a real True marks the current page, like PHP's strict `=== true`
        current = " aria-current=\"page\"" if item.get("active") is True else ""


        html = f"<li><a class=\"icf02-sidebar-link\" href=\"{Sidebar._escape(item_url)}\"{current}>"
        html += Sidebar._render_icon(item.get("icon"))
        # Raises a ValueError on an unknown icon name
        html += Sidebar._escape(item_label)
        html += "</a></li>"
        return html


    @staticmethod
    def _render_action(action: Any)-> str:
        """
        Build the HTML of one footer action: an icon-only link or button, named by its label.

        ### Parameters
        - action (`Any`): The action, as described for the `actions` argument of `__init__()`.

        ### Returns
        - `str`: The action's `<a>` or `<button>`.

        ### Raises
        - `ValueError`: If the action is not a dict, has no label or icon, has an unknown key or names an unknown icon.
        """
        # Validate the action
        action_label = action.get("label") if isinstance(action, dict) else None
        action_icon = action.get("icon") if isinstance(action, dict) else None
        if action_label is None or action_label == "" or action_icon is None or action_icon == "":
            raise ValueError("Every sidebar action needs a label and an icon")
        for key in action:
            if key not in Sidebar._VALID_ACTION_KEYS:
                raise ValueError(f"Unknown sidebar action key: {Sidebar._to_string(key)}")
        action_url = action.get("url")
        is_link = action_url is not None and action_url != ""


        # The label names the icon-only control for screen readers and shows as a tooltip
        all_attributes: dict[str, Any] = {"class": "icf02-sidebar-action"}
        if is_link:
            all_attributes["href"] = action_url
        else:
            all_attributes["type"] = "button"
        all_attributes["aria-label"] = action_label
        all_attributes["title"] = action_label
        for key, value in (action.get("attributes") or {}).items():
            if key == "class":
                all_attributes["class"] += " " + Sidebar._to_string(value)
            else:
                all_attributes[key] = value

        tag = "a" if is_link else "button"
        icon_html = Sidebar._render_icon(action_icon)
        # Raises a ValueError on an unknown icon name
        return f"<{tag}" + Sidebar._attributes_to_html(all_attributes) + ">" + icon_html + f"</{tag}>"


    @staticmethod
    def _render_icon(icon: str | None)-> str:
        """
        Build the HTML of an icon, hidden from screen readers because the text next to it (or the label) names it.

        ### Parameters
        - icon (`str`, optional): A bundled icon's name or the URL of your own SVG icon. `None` or "" gives no icon.

        ### Returns
        - `str`: The icon's `<span>`, or "" without an icon.

        ### Raises
        - `ValueError`: If `icon` names an unknown icon.
        """
        if icon is None or icon == "":
            return ""
        icon_style = "--icon-url: url('" + Sidebar._escape_css_string(DesignSystem.resolve_icon(icon)) + "')"
        # Raises a ValueError on an unknown icon name
        return f"<span class=\"icon\" style=\"{Sidebar._escape(icon_style)}\" aria-hidden=\"true\"></span>"


    @staticmethod
    def _attributes_to_html(attributes: dict[str, Any])-> str:
        """
        Turn name: value pairs into an escaped attribute string with a leading space.

        ### Parameters
        - attributes (`dict[str, Any]`): The attributes as name: value.
        `True` writes a bare attribute (e.g., ` hidden`), `False` and `None` are skipped.

        ### Returns
        - `str`: The attributes as HTML (e.g., ` class="icf02-sidebar" id="sidebar"`).
        """
        html = ""
        for key, value in attributes.items():
            if value is None or value is False:
                continue
            html += " " + Sidebar._escape(key)
            if value is not True:
                html += f"=\"{Sidebar._escape(value)}\""
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
            Sidebar._to_string(value)
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
            Sidebar._to_string(value)
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
