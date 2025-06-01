<?php

namespace NativePlatform\SubContainer\Style;

interface UiInterface
{
    /**
     * Renders a UI component with the given name and attributes.
     *
     * Example:
     *   render('button', ['label' => 'Submit', 'type' => 'submit'])
     *   → returns <button type="submit" class="...">Submit</button>
     *
     * @param string $kit The kit name, e.g. 'actions', 'layout' etc.
     * @param string $component The component name, e.g. 'button', 'dropdown', 'base'
     * @param array $attributes Optional associative array of HTML attributes or component parameters
     * @return string Rendered HTML output of the component
     */
    public function render(string $kit, string $component, array $attributes = []): string;

    /**
     * Return an array of <link> tags or style includes
     * @return string[]
     */
    public function getStyles(): array;

    /**
     * Return an array of <script> tags or JS includes
     * @return string[]
     */
    public function getScripts(): array;
}
