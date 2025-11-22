<?php
// FILE: /app/core/View.php

/**
 * View Class
 * Handles rendering of view templates
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class View {

    /**
     * Render a view template
     * @param string $view View file path (without .php extension)
     * @param array $data Data to pass to view
     * @param string $layout Layout file (default: 'main')
     */
    public function render($view, $data = [], $layout = 'main') {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die("View not found: $view");
        }

        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();

        // Include the view file
        require $viewFile;

        // Get the view content
        $content = ob_get_clean();

        // If layout is specified, wrap content in layout
        if ($layout) {
            $layoutFile = __DIR__ . '/../views/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }
}
