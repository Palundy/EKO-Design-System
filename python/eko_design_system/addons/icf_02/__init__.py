"""
The ICF-02 addon: the components that only the ICF-02 application needs.
The Python counterpart of the PHP namespace `EKO\\DesignSystem\\Addons\\Icf02`.
```python
from eko_design_system import DesignSystem
from eko_design_system.addons.icf_02 import Icf02, Sidebar

DesignSystem.enable_addon(Icf02)
```
With Flask, pass the addon to the extension instead (`EkoDesignSystem(app, addons = [Icf02])`)
and use `{{ eko.icf_02.Sidebar(...) }}` in templates.
"""
from .icf_02 import Icf02
from .pincode import Pincode
from .sidebar import Sidebar


__all__ = ["Icf02", "Pincode", "Sidebar"]
