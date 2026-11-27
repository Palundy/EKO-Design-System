class Addon:
    """
    `Addon` is the shared base of every addon descriptor. It is not used directly.

    An addon is a set of components that only one application needs (e.g., EKO Data). It sits next to the core:
    its CSS, JavaScript and icons live in `assets/addons/<name>/`, its Python classes in `eko_design_system/addons/<name>/`
    (e.g., `eko_design_system.addons.eko_data`). Addons may use the core, but the core never uses an addon,
    and addons do not use each other. `eko_design_system.addons` is a namespace package (it has no `__init__.py`),
    so an addon can later move to a package of its own without changing its import path.

    Each addon has one descriptor class that extends `Addon` and fills in its constants:
    ```python
    class EkoData(Addon):
        NAME = "eko-data"
        STYLESHEETS = ("css/eko-data.css",)
    ```
    An app switches an addon on once, before the page head is rendered, so `DesignSystem.stylesheet()`
    and `DesignSystem.scripts()` also render the addon's tags: `DesignSystem.enable_addon(EkoData)`.
    With Flask, pass it to the extension instead: `EkoDesignSystem(app, addons = [EkoData])`.
    Import it with `from eko_design_system import Addon`.

    The Python counterpart of the PHP `EKO\\DesignSystem\\Addon`.
    """


    NAME: str = ""
    """
    The addon's name in kebab-case (e.g., "eko-data"). It is the addon's folder in `assets/addons/`,
    its CSS sub-layer (`addons.<name>`) and the prefix of its icon names (e.g., "eko-data:gauge-outline").
    """

    STYLESHEETS: tuple[str, ...] = ()
    """The addon's stylesheets, relative to its folder (e.g., "css/eko-data.css"). Linked after the core stylesheet, in this order."""

    SCRIPTS: tuple[str, ...] = ()
    """The addon's scripts, relative to its folder (e.g., "js/charts.js"). Loaded after the core scripts, in this order."""


    @classmethod
    def path(
        cls,
        file: str
    )-> str:
        """
        Build the path of a file inside the addon's folder, relative to the design system's folder.
        Pass it to `DesignSystem.url()` to get the file's URL.

        ### Parameters
        - file (`str`): The file's path inside the addon's folder (e.g., "css/eko-data.css").

        ### Returns
        - `str`: The path (e.g., "assets/addons/eko-data/css/eko-data.css").
        """
        return f"assets/addons/{cls.NAME}/" + str(file).lstrip("/")
