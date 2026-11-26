<?php
namespace EKO\DesignSystem;

use Stringable;


/**
 * `Component` is the shared base of every design-system component. It is not used directly.
 *
 * A component builds its HTML in the constructor and returns it as a string, without printing anything:
 * ```php
 * <?= new Button("Save") ?>                   // print it in a template
 * echo new Button("Save");                    // print it from PHP code
 * $html = (string) new Button("Save");        // keep it as a string
 * $html = "<p>" . new Button("Save") . "</p>"; // use it inside other HTML
 * ```
 * Because a component is `Stringable`, it can be passed wherever a string is expected,
 * such as a template engine's variables.
 */
abstract class Component implements Stringable {


    /** The generated HTML. Set by the constructor of every component. */
    public $html = null;


    /**
     * Returns the component's HTML, so the component can be printed or used as a string.
     *
     * @return string
     * The generated HTML.
     */
    public function __toString(): string {
        return (string) $this->html;
    }
}
