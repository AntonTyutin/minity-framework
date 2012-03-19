<?php

use Minity\Controller\AbstractController;
use Minity\Http\Response;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class DefaultController extends AbstractController
{
    public function actionIndex()
    {
        return new Response('');
    }

    public function actionList()
    {
        return new Response('');
    }
}
