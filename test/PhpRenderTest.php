<?php

require_once 'vfsStream/vfsStream.php';

use Minity\View\PhpRenderer;

/**
 */
class PhpRenderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        vfsStream::setup('root');
    }

    public function testRender()
    {
        $templateName = 'test';
        $template = 'Hello <?php echo $name ?>!';
        $this->createTemplateFile($templateName, $template);
        $renderer = new PhpRenderer(vfsStream::url('root'));
        $result = $renderer->render(
            $templateName,
            array('name' => 'World')
        );
        $this->assertEquals('Hello World!', $result);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRenderDirNotFound()
    {
        new PhpRenderer('/unexistentTemplateDir');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRenderFileNotFound()
    {
        $render = new PhpRenderer(vfsStream::url('root'));
        $render->render('unexistent', array());
    }

    protected function createTemplateFile($templateName, $template)
    {
        $templateFile = vfsStream::url('root') . '/' . $templateName . '.php';
        file_put_contents($templateFile, $template);
    }
}
