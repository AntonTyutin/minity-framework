<?php

namespace Minity\Controller;

use Minity\Http\Response;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class ErrorController extends AbstractController
{
    /**
     * 404
     */
    public function actionNotFound()
    {
        return new Response('404 - Страница не найдена', 404);
    }

    /**
     * 403
     */
    public function actionDenied()
    {
        return new Response('403 - В доступе отказано', 403);
    }

    /**
     * 500
     */
    public function actionCrash()
    {
        $e = $this->getRequest()->get('exception');
        if ($e) {
            $content = sprintf(
                '<h1>%s</h1><h2>%s</h2><pre>%s</pre>',
                '500 - Ошибка приложения',
                $e->getMessage(),
                $e->getTraceAsString()
            );
        } else {
            $content = sprintf(
                '<h1>%s</h1>',
                '500 - Ошибка приложения'
            );
        }
        return new Response(nl2br($content), 500);
    }
}
