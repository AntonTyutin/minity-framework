<?php

namespace Minity\View;

require __DIR__ . '/php_template_helpers.php';

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class PhpRenderer
{

    function __construct($templateDir)
    {
        if (!file_exists($templateDir)) {
            throw new \RuntimeException(
                'Директория содержащая файлы шаблонов не существует'
            );
        }
        $this->templateDir = $templateDir;
    }


    public function render($template, $data)
    {
        $filename = $this->getTemplateFilename($template);
        if (!file_exists($filename)) {
            throw new \RuntimeException(
                sprintf('Не найден файл с шаблоном "%s"', $template)
            );
        }
        if (!is_readable($filename)) {
            throw new \RuntimeException(
                sprintf(
                    'Файл с шаблоном "%s" не доступен для чтения',
                    $template
                )
            );
        }
        return self::renderTemplate($filename, $data);

    }

    private static function renderTemplate($filename, $data)
    {
        extract($data);
        ob_start();
        include $filename;
        return ob_get_clean();
    }

    private function getTemplateFilename($template)
    {
        return $this->templateDir .'/' . $template . '.php';
    }
}
