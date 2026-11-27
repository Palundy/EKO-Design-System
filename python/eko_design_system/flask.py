import importlib
from collections.abc import Iterable
from pathlib import Path

from flask import Blueprint, Flask
from markupsafe import Markup

from eko_design_system import Addon, Button, DesignSystem, SectionHeading, SpecTable
from eko_design_system.cards import ApplicationCard, ArticleCard, ContactCard, IndustryCard, ProductCard
from eko_design_system.fields import Dropdown, Input, Textarea
from eko_design_system.navigation import Breadcrumb, NavItem


class TemplateHelpers:
    """
    `TemplateHelpers` is what Jinja templates see as `eko`: the page-head helpers and every component.

    ```html
    <head>
        {{ eko.stylesheet() }}
        {{ eko.scripts() }}
    </head>
    <body>
        {{ eko.Input("Email address", name = "email", type = "email", required = True) }}
        {{ eko.Button("Send", type = "submit", icon = "envelope-outline") }}
    </body>
    ```
    Components print as HTML and are not escaped a second time; everything passed to them is escaped by the component.
    The components of every addon passed to `EkoDesignSystem` sit under the addon's name in snake_case,
    e.g. `{{ eko.eko_data.StatusTracker(...) }}`.
    """


    Button = Button
    SectionHeading = SectionHeading
    SpecTable = SpecTable
    Input = Input
    Textarea = Textarea
    Dropdown = Dropdown
    ProductCard = ProductCard
    ContactCard = ContactCard
    ApplicationCard = ApplicationCard
    IndustryCard = IndustryCard
    ArticleCard = ArticleCard
    Breadcrumb = Breadcrumb
    NavItem = NavItem


    @staticmethod
    def stylesheet()-> Markup:
        """
        Build the `<link>` tag for the design system's stylesheet, marked as safe HTML for Jinja.

        ### Returns
        - `Markup`: The tag's HTML.
        """
        return Markup(DesignSystem.stylesheet())


    @staticmethod
    def scripts()-> Markup:
        """
        Build the `<script>` tags the components need, marked as safe HTML for Jinja.

        ### Returns
        - `Markup`: The tags' HTML.
        """
        return Markup(DesignSystem.scripts())


    @staticmethod
    def icon(name: str)-> str:
        """
        Build the URL of a bundled icon (e.g., for your own `<span class="icon">`).

        ### Parameters
        - name (`str`): The icon's file name without `.svg` (e.g., "envelope-outline").

        ### Returns
        - `str`: The icon's URL.

        ### Raises
        - `ValueError`: If the icon does not exist.
        """
        return DesignSystem.icon(name)


class EkoDesignSystem:
    """
    `EkoDesignSystem` connects the design system to a Flask app in one line:
    ```python
    from flask import Flask
    from eko_design_system.flask import EkoDesignSystem

    app = Flask(__name__)
    EkoDesignSystem(app)
    ```

    It does three things:
    1. It serves the design system's CSS, JavaScript, fonts and icons straight from the installed package,
    at `/eko-design-system/assets/...`. There is nothing to copy or publish, and it works offline.
    2. It sets `DesignSystem`'s base URL to match, so the components' icon URLs and the stylesheet and script tags point there.
    3. It makes `eko` available in every Jinja template (see `TemplateHelpers`): `{{ eko.stylesheet() }}`,
    `{{ eko.scripts() }}` and every component, such as `{{ eko.Button("Save") }}`.

    With an application factory, create it first and connect it later: `design_system = EkoDesignSystem()`,
    then `design_system.init_app(app)` inside the factory.
    The URL can be changed with the `url_prefix` argument or the `EKO_DESIGN_SYSTEM_URL_PREFIX` config key.
    `DesignSystem`'s base URL is shared by the whole Python process, so give every app in one process the same prefix.

    Addons are switched on with the `addons` argument, e.g. `EkoDesignSystem(app, addons = [EkoData])`.
    Their files are served with the rest of the assets, and their components appear in templates under the addon's name
    (`{{ eko.eko_data.StatusTracker(...) }}`). The enabled addons are shared by the whole Python process too.
    """


    DEFAULT_URL_PREFIX: str = "/eko-design-system"
    """The URL the design system is served from when no other prefix is configured."""

    _ASSET_FOLDERS: tuple[Path, ...] = (
        Path(__file__).resolve().parent / "static" / "assets",
        Path(__file__).resolve().parents[2] / "assets",
    )
    """
    The folders that may hold the assets on disk: the copy inside an installed package first,
    then the repository's own `assets/` (when running from a checkout).
    """


    def __init__(
        self,
        app: Flask | None = None,
        url_prefix: str | None = None,
        addons: Iterable[type[Addon]] = ()
    )-> None:
        """
        Create the extension, and connect it to `app` straight away when one is given.

        ### Parameters
        - app (`Flask`, optional): The app to connect. Leave it out with an application factory and call `init_app()` later.
        - url_prefix (`str`, optional): The URL the design system is served from. Defaults to the
        `EKO_DESIGN_SYSTEM_URL_PREFIX` config key, or "/eko-design-system".
        - addons (`Iterable[type[Addon]]`, optional): The addons to switch on (e.g., `[EkoData]`).

        ### Returns
        - `None`

        ### Raises
        - `RuntimeError`: If the design system's assets cannot be found.
        - `ValueError`: If one of `addons` is not an addon.
        """
        self._url_prefix = url_prefix
        self._addons = tuple(addons)
        if app is not None:
            self.init_app(app)
            # Raises a RuntimeError when the assets are missing, a ValueError on an invalid addon


    def init_app(
        self,
        app: Flask
    )-> None:
        """
        Connect the design system to a Flask app: serve the assets, set the base URL and add `eko` to the templates.

        ### Parameters
        - app (`Flask`): The app to connect.

        ### Returns
        - `None`

        ### Raises
        - `RuntimeError`: If the design system's assets cannot be found.
        - `ValueError`: If one of the addons is not an addon.
        """
        # Switch the addons on first, so an invalid one fails before anything is registered
        for addon in self._addons:
            DesignSystem.enable_addon(addon)
            # Raises a ValueError when it is not an addon

        url_prefix = self._url_prefix or app.config.get("EKO_DESIGN_SYSTEM_URL_PREFIX", self.DEFAULT_URL_PREFIX)
        url_prefix = "/" + url_prefix.strip("/")

        # Serve only the assets folder, so nothing else from the package (or a checkout) is reachable
        blueprint = Blueprint(
            name = "eko_design_system",
            import_name = __name__,
            static_folder = str(self._asset_folder()),
            static_url_path = "",
            url_prefix = f"{url_prefix}/assets"
        )
        # Raises a RuntimeError when the assets are missing
        app.register_blueprint(blueprint)

        # Point the components at the served files, and give every template the helpers
        DesignSystem.set_base_url(url_prefix)
        app.jinja_env.globals["eko"] = self._template_helpers()
        app.extensions["eko_design_system"] = self


    def _template_helpers(self)-> type[TemplateHelpers]:
        """
        Build the `eko` template global: `TemplateHelpers`, plus each addon's package under the addon's name in snake_case
        (e.g., `eko.eko_data` is `eko_design_system.addons.eko_data`, so templates can use `eko.eko_data.StatusTracker`).
        A subclass is made per app, so the addons of one app do not show up in another app's templates.

        ### Returns
        - `type[TemplateHelpers]`: The helpers for this app's templates.
        """
        if not self._addons:
            return TemplateHelpers

        # The descriptor lives in its addon's package (e.g., eko_design_system.addons.eko_data.eko_data), so take its parent
        packages = {
            addon.NAME.replace("-", "_"): importlib.import_module(addon.__module__.rpartition(".")[0])
            for addon in self._addons
        }
        return type("TemplateHelpers", (TemplateHelpers,), packages)


    @staticmethod
    def _asset_folder()-> Path:
        """
        Find the folder that holds the design system's assets on disk.

        ### Returns
        - `Path`: The first of `_ASSET_FOLDERS` that contains `css/main.css`.

        ### Raises
        - `RuntimeError`: If none of them does.
        """
        for folder in EkoDesignSystem._ASSET_FOLDERS:
            if (folder / "css" / "main.css").is_file():
                return folder
        raise RuntimeError("The EKO design system's assets are missing. Reinstall the eko-design-system package.")
