<?php

namespace PHPvian\Views;

class View
{
    /**
     * Import a view
     */
    public function load($path, array $data)
    {
        // Construct the absolute path to the preview file
        $viewFile = dirname(__DIR__) . "/../resources/views/{$path}.php";

        // Checks if the preview file exists
        if (file_exists($viewFile)) {
            // Extract data to local variables
            extract($data);

            // Include the preview file
            require_once $viewFile;
        } else {
            // If the preview file does not exist, throw an error
            throw new \Exception("Preview file not found: $viewFile");
        }
    }

}