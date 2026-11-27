<?php
namespace EKO\DesignSystem\Addons\Icf02;

use EKO\DesignSystem\Addon;


/**
 * `Icf02` describes the ICF-02 addon: the components that only the ICF-02 application (indoor pyranometer calibrations) needs,
 * such as `Sidebar`. Its CSS lives in `assets/addons/icf-02/`, and its class names and tokens use the `icf02-` prefix.
 * Import it with `use EKO\DesignSystem\Addons\Icf02\Icf02;` after requiring Composer's `vendor/autoload.php`.
 *
 * Switch it on once, before the page head is printed:
 * ```php
 * use EKO\DesignSystem\DesignSystem;
 * use EKO\DesignSystem\Addons\Icf02\Icf02;
 * use EKO\DesignSystem\Addons\Icf02\Sidebar;
 *
 * DesignSystem::enableAddon(Icf02::class);
 * ...
 * <?= DesignSystem::stylesheet() ?>   // the core stylesheet and icf-02.css
 * ```
 */
class Icf02 extends Addon {


    /** The addon's name: its folder in `assets/addons/`, its CSS sub-layer and the prefix of its icon names. */
    public const NAME = 'icf-02';

    /** The addon's stylesheet, linked after the core stylesheet. */
    public const STYLESHEETS = [
        'css/icf-02.css',
    ];

    /** The addon's scripts, loaded after the core scripts: the pincode's keypad, keyboard input and animations. */
    public const SCRIPTS = [
        'js/pincode.js',
    ];
}
