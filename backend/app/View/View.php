<?php

declare(strict_types=1);

namespace AmarMayor\View;

use RuntimeException;

/**
 * Lightweight Explicit Server-Rendered PHP View Engine.
 */
class View
{
    private static string $viewsPath = '';

    public static function setViewsPath(string $path): void
    {
        self::$viewsPath = rtrim($path, '/\\');
    }

    public static function render(string $template, array $data = [], ?string $layout = 'layouts/app'): string
    {
        $templatePath = self::resolvePath($template);
        if (!file_exists($templatePath)) {
            throw new RuntimeException("View template not found: [{$template}] at {$templatePath}");
        }

        // Render view content
        $content = self::renderFile($templatePath, $data);

        // If a layout is specified, wrap content inside the layout
        if ($layout !== null) {
            $layoutPath = self::resolvePath($layout);
            if (file_exists($layoutPath)) {
                $layoutData = array_merge($data, ['content' => $content]);
                return self::renderFile($layoutPath, $layoutData);
            }
        }

        return $content;
    }

    public static function partial(string $partial, array $data = []): string
    {
        $partialPath = self::resolvePath($partial);
        if (!file_exists($partialPath)) {
            throw new RuntimeException("Partial view not found: [{$partial}] at {$partialPath}");
        }
        return self::renderFile($partialPath, $data);
    }

    private static function renderFile(string $filePath, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            require $filePath;
            return (string)ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    private static function resolvePath(string $name): string
    {
        $normalized = str_replace('.', DIRECTORY_SEPARATOR, $name);
        return self::$viewsPath . DIRECTORY_SEPARATOR . $normalized . '.php';
    }
}
