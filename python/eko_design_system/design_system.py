import re
from pathlib import Path

from eko_design_system.addon import Addon


class DesignSystem:
    """
    `DesignSystem` holds the settings that all components share: where the design system's static files
    (the `assets/` folder with the CSS, JavaScript, fonts and icons) are served from. Every URL the components build to those files goes through it,
    so an app can serve the design system from any folder, such as `/design-system/`.

    By default the files are expected at the web root (`/assets/css/main.css`, `/assets/icons/...`).
    Set the base URL once, before any component is rendered:
    ```python
    from eko_design_system import DesignSystem

    DesignSystem.set_base_url("/design-system")
    ```

    Then use the helpers in the page head, and pass icon names to the components:
    ```html
    <head>
        {{ DesignSystem.stylesheet() }}
        {{ DesignSystem.scripts() }}
    </head>
    ```

    Addons (application-specific components, see `Addon`) are switched on here too, before the page head is rendered.
    `stylesheet()` and `scripts()` then also render the tags of every enabled addon:
    ```python
    from eko_design_system.addons.eko_data import EkoData

    DesignSystem.enable_addon(EkoData)
    ```

    The Python counterpart of the PHP `EKO\\DesignSystem\\DesignSystem`, with the same URLs and checks.
    """


    _PACKAGE_ROOTS: tuple[Path, ...] = (
        Path(__file__).resolve().parent / "static",
        Path(__file__).resolve().parents[2],
    )
    """
    The folders that may hold `assets/` on disk, used to check that icons exist: the copy inside an installed
    package first, then the repository root (when running from a checkout).
    """

    _ICON_NAME_PATTERN: re.Pattern = re.compile(r"^[a-z0-9]+(-[a-z0-9]+)*$")
    """
    The pattern of an icon name (e.g., "envelope-outline"): lowercase letters and digits, separated by single hyphens.
    Icon arguments that match neither this nor `_ADDON_ICON_NAME_PATTERN` are treated as URLs.
    """

    _ADDON_ICON_NAME_PATTERN: re.Pattern = re.compile(r"^([a-z0-9]+(-[a-z0-9]+)*):([a-z0-9]+(-[a-z0-9]+)*)$")
    """
    The pattern of an addon's icon name: the addon's name, a colon and the icon name (e.g., "eko-data:gauge-outline").
    Group 1 is the addon, group 3 the icon.
    """

    _ABSOLUTE_URL_PATTERN: re.Pattern = re.compile(r"^([a-z][a-z0-9+.-]*:)?//", re.IGNORECASE)
    """The pattern of a full URL with a scheme (e.g., "https://") or a protocol-relative URL ("//")."""


    _base_url: str = "/"
    """The URL the design system's folder is served from. It always ends with a slash."""

    _addons: dict[str, type[Addon]] = {}
    """The enabled addons, as name: descriptor class, in the order they were enabled."""


    @staticmethod
    def set_base_url(base_url: str)-> None:
        """
        Set the URL the design system's folder is served from.
        This is the folder that contains `assets/`.

        A path is always made root-relative: "design-system" becomes "/design-system/".
        A relative URL cannot work, because icon URLs are passed to the CSS in the `--icon-url` custom property,
        and browsers resolve a relative URL in a custom property against the stylesheet that uses it (icon.css),
        not against the page. The icon would then be looked for inside the CSS folder.

        ### Parameters
        - base_url (`str`): A path from the web root (e.g., "/design-system"; the leading slash is optional),
        a full URL (e.g., "https://intranet.example.com/design-system") or a protocol-relative URL
        (e.g., "//cdn.example.com/ds"). A trailing slash is optional.

        ### Returns
        - `None`
        """
        base_url = str(base_url).strip()

        # Full and protocol-relative URLs are used as given, anything else is a path from the web root
        if DesignSystem._ABSOLUTE_URL_PATTERN.match(base_url):
            DesignSystem._base_url = base_url.rstrip("/") + "/"
            return
        path = base_url.strip("/")
        DesignSystem._base_url = "/" if path == "" else f"/{path}/"


    @staticmethod
    def get_base_url()-> str:
        """
        Return the URL the design system's folder is served from.

        ### Returns
        - `str`: The base URL, ending with a slash (e.g., "/design-system/").
        """
        return DesignSystem._base_url


    @staticmethod
    def url(path: str)-> str:
        """
        Build the URL of a file inside the design system's folder.

        ### Parameters
        - path (`str`): The file's path inside the design system's folder (e.g., "assets/css/main.css").

        ### Returns
        - `str`: The file's URL (e.g., "/design-system/assets/css/main.css"). It is not HTML-escaped.
        """
        return DesignSystem._base_url + str(path).lstrip("/")


    @staticmethod
    def enable_addon(addon: type[Addon])-> None:
        """
        Switch an addon on: `stylesheet()` and `scripts()` then also render its tags.
        Enabling the same addon twice has no further effect.

        ### Parameters
        - addon (`type[Addon]`): The addon's descriptor class, a subclass of `Addon` (e.g., `EkoData`).

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `addon` is not a subclass of `Addon` or has no valid `NAME`.
        """
        if not isinstance(addon, type) or not issubclass(addon, Addon) or addon is Addon:
            raise ValueError(f"Not a design system addon: {getattr(addon, '__name__', type(addon).__name__)}")
        if not DesignSystem._ICON_NAME_PATTERN.match(str(addon.NAME)):
            raise ValueError(f"Invalid addon name: {addon.NAME}")
        DesignSystem._addons[addon.NAME] = addon


    @staticmethod
    def get_addons()-> list[type[Addon]]:
        """
        Return the enabled addons.

        ### Returns
        - `list[type[Addon]]`: The descriptor classes, in the order they were enabled.
        """
        return list(DesignSystem._addons.values())


    @staticmethod
    def icon(name: str)-> str:
        """
        Build the URL of a bundled icon: a core icon (e.g., "envelope-outline", from `assets/icons/`)
        or an addon's icon, prefixed with the addon's name (e.g., "eko-data:gauge-outline", from `assets/addons/eko-data/icons/`).
        An addon's icons can be used without enabling the addon.

        ### Parameters
        - name (`str`): The icon's file name without `.svg`, with an addon prefix for an addon's icon.

        ### Returns
        - `str`: The icon's URL (e.g., "/assets/icons/envelope-outline.svg"). It is not HTML-escaped.

        ### Raises
        - `ValueError`: If `name` is not a valid icon name or the icon does not exist.
        """
        name = str(name)
        addon_match = DesignSystem._ADDON_ICON_NAME_PATTERN.match(name)
        if addon_match:
            path = f"assets/addons/{addon_match.group(1)}/icons/{addon_match.group(3)}.svg"
        elif DesignSystem._ICON_NAME_PATTERN.match(name):
            path = f"assets/icons/{name}.svg"
        else:
            raise ValueError(f"Invalid icon name: {name}")
        if not any((root / path).is_file() for root in DesignSystem._PACKAGE_ROOTS):
            raise ValueError(f"Unknown icon: {name}")
        return DesignSystem.url(path)


    @staticmethod
    def resolve_icon(icon: str)-> str:
        """
        Turn a component's icon argument into a URL.

        An icon name (e.g., "envelope-outline" or "eko-data:gauge-outline") becomes the bundled icon's URL,
        anything else is returned unchanged, so components accept both names and URLs of your own icons (e.g., "/images/my-icon.svg").

        ### Parameters
        - icon (`str`): An icon name or a URL.

        ### Returns
        - `str`: The icon's URL. It is not HTML-escaped.

        ### Raises
        - `ValueError`: If `icon` looks like an icon name but the icon does not exist.
        """
        if DesignSystem._ICON_NAME_PATTERN.match(str(icon)) or DesignSystem._ADDON_ICON_NAME_PATTERN.match(str(icon)):
            return DesignSystem.icon(icon)
            # Raises a ValueError on an unknown icon
        return str(icon)


    @staticmethod
    def stylesheet()-> str:
        """
        Build the `<link>` tags for the design system's stylesheet, followed by those of every enabled addon.

        ### Returns
        - `str`: The tags' HTML, one per line (e.g., `<link rel="stylesheet" href="/assets/css/main.css">`).
        """
        paths = ["assets/css/main.css"]
        for addon in DesignSystem._addons.values():
            paths += [addon.path(file) for file in addon.STYLESHEETS]

        return "\n".join(
            "<link rel=\"stylesheet\" href=\"" + DesignSystem._escape(DesignSystem.url(path)) + "\">"
            for path in paths
        )


    @staticmethod
    def scripts()-> str:
        """
        Build the `<script>` tags the components need (`assets/js/dropdowns.js`, for `fields.Dropdown`),
        followed by those of every enabled addon.

        ### Returns
        - `str`: The tags' HTML, one per line.
        """
        paths = ["assets/js/dropdowns.js"]
        for addon in DesignSystem._addons.values():
            paths += [addon.path(file) for file in addon.SCRIPTS]

        return "\n".join(
            "<script src=\"" + DesignSystem._escape(DesignSystem.url(path)) + "\" defer></script>"
            for path in paths
        )


    @staticmethod
    def _escape(value: str)-> str:
        """
        Escape a value for use in HTML text or a quoted attribute, like PHP's `htmlspecialchars($value, ENT_QUOTES)`.

        ### Parameters
        - value (`str`): The value to escape.

        ### Returns
        - `str`: The escaped value.
        """
        return (
            str(value)
            .replace("&", "&amp;")
            .replace("<", "&lt;")
            .replace(">", "&gt;")
            .replace("\"", "&quot;")
            .replace("'", "&#039;")
        )
