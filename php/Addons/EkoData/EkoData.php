<?php
namespace EKO\DesignSystem\Addons\EkoData;

use EKO\DesignSystem\Addon;


/**
 * `EkoData` describes the EKO Data addon: the components that only the EKO Data application needs,
 * such as `StatusTracker`. Its CSS lives in `assets/addons/eko-data/`, and its class names and tokens use the `ekodata-` prefix.
 * Import it with `use EKO\DesignSystem\Addons\EkoData\EkoData;` after requiring Composer's `vendor/autoload.php`.
 *
 * Switch it on once, before the page head is printed:
 * ```php
 * use EKO\DesignSystem\DesignSystem;
 * use EKO\DesignSystem\Addons\EkoData\EkoData;
 * use EKO\DesignSystem\Addons\EkoData\StatusTracker;
 *
 * DesignSystem::enableAddon(EkoData::class);
 * ...
 * <?= DesignSystem::stylesheet() ?>   // the core stylesheet and eko-data.css
 * ```
 */
class EkoData extends Addon {


    /** The addon's name: its folder in `assets/addons/`, its CSS sub-layer and the prefix of its icon names. */
    public const NAME = 'eko-data';

    /** The addon's stylesheet, linked after the core stylesheet. */
    public const STYLESHEETS = [
        'css/eko-data.css',
    ];

    /** The addon needs no scripts yet. */
    public const SCRIPTS = [];
}
