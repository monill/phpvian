<?php

namespace PHPvian\Views;

use Exception;

class View
{
    protected $basePath;
    protected $cacheEnabled;
    protected $cacheDir;

    public function __construct()
    {
        $this->basePath = dirname(__DIR__) . '/../resources/views/';
        $this->cacheEnabled = config('settings', 'cacheEnabled');
        $this->cacheDir = dirname(__DIR__) . '/../storage/cache/';;
    }

    /**
     * Load a view
     *
     * @param string $path The path to the view file relative to the views directory
     * @param array $data The data to pass to the view
     * @throws \Exception If the view file is not found
     */
    public function load($path, array $data = [])
    {
        $viewFile = $this->getViewFilePath($path);

        if (file_exists($viewFile)) {
            $this->renderView($viewFile, $data);
        } else {
            throw new Exception("View file not found: $path");
        }
    }

    /**
     * Get the absolute path to the view file
     *
     * @param string $path The path to the view file relative to the views directory
     * @return string The absolute path to the view file
     */
    protected function getViewFilePath($path)
    {
        return $this->basePath . "{$path}.php";
    }

    /**
     * Render the view file with the given data
     *
     * @param string $viewFile The absolute path to the view file
     * @param array $data The data to pass to the view
     */
    protected function renderView($viewFile, array $data)
    {
        if ($this->cacheEnabled && $this->cacheDir) {
            $cacheFile = $this->cacheDir . md5($viewFile) . '.php';
            if (file_exists($cacheFile)) {
                include $cacheFile;
                return;
            }
        }

        ob_start();
        extract($data);
        include $viewFile;
        $content = ob_get_clean();

        echo $content;

        if ($this->cacheEnabled && $this->cacheDir && isset($cacheFile)) {
            file_put_contents($cacheFile, $content);
        }
    }

    /**
     * Render a partial view
     *
     * @param string $path The path to the partial view file relative to the views directory
     * @param array $data The data to pass to the partial view
     */
    public function renderPartial($path, array $data = [])
    {
        $viewFile = $this->getViewFilePath($path);
        if (file_exists($viewFile)) {
            ob_start();
            extract($data);
            include $viewFile;
            echo ob_get_clean();
        } else {
            throw new Exception("Partial view file not found: $path");
        }
    }

    /**
     * Render a view as JSON
     *
     * @param mixed $data The data to encode as JSON
     */
    public function renderJson($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Render a view as XML
     *
     * @param array $data The data to convert to XML
     */
    public function renderXml(array $data)
    {
        header('Content-Type: application/xml');
        echo $this->arrayToXml($data);
    }

    /**
     * Convert an array to XML
     *
     * @param array $array The array to convert
     * @param string $rootNodeName The root node name
     * @return string The XML string
     */
    protected function arrayToXml(array $array, $rootNodeName = 'data')
    {
        $xml = new \SimpleXMLElement("<?xml version=\"1.0\"?><$rootNodeName></$rootNodeName>");
        $this->arrayToXmlHelper($array, $xml);
        return $xml->asXML();
    }

    /**
     * Helper function to convert an array to XML
     *
     * @param array $array The array to convert
     * @param \SimpleXMLElement $xml The XML element to append to
     */
    protected function arrayToXmlHelper(array $array, \SimpleXMLElement &$xml)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subNode = $xml->addChild("$key");
                    $this->arrayToXmlHelper($value, $subNode);
                } else {
                    $subNode = $xml->addChild("item$key");
                    $this->arrayToXmlHelper($value, $subNode);
                }
            } else {
                $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    /**
     * Inject dependencies into the view
     *
     * @param string $className The class name of the dependency
     * @param mixed $dependency The dependency object
     */
    public function inject($className, $dependency)
    {
        $this->{$className} = $dependency;
    }

    /**
     * Fire an event before rendering a view
     *
     * @param string $eventName The name of the event
     */
    public function fireEvent($eventName)
    {
        // Implement event firing logic here
    }

    /**
     * Preprocess a view before rendering
     *
     * @param string $viewPath The path to the view file
     * @return string The preprocessed view content
     */
    public function preprocess($viewPath)
    {
        // Implement preprocessing logic here
        return file_get_contents($viewPath);
    }

    /**
     * Handle errors during view rendering
     *
     * @param \Throwable $error The error that occurred
     */
    public function handleError(\Throwable $error)
    {
        // Implement error handling logic here
        echo "An error occurred: " . $error->getMessage();
    }
}
