"""
The EKO Data addon: the components that only the EKO Data application needs.
The Python counterpart of the PHP namespace `EKO\\DesignSystem\\Addons\\EkoData`.
```python
from eko_design_system import DesignSystem
from eko_design_system.addons.eko_data import EkoData, StatusTracker

DesignSystem.enable_addon(EkoData)
```
With Flask, pass the addon to the extension instead (`EkoDesignSystem(app, addons = [EkoData])`)
and use `{{ eko.eko_data.StatusTracker(...) }}` in templates.
"""
from .eko_data import EkoData
from .status_tracker import StatusTracker


__all__ = ["EkoData", "StatusTracker"]
