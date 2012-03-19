<?php

namespace Minity\Controller;

use Minity\Http\NotFoundException;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class Router
{
    /**
     * Правила раутинга
     * @var array
     */
    protected $rules;

    function __construct(array $rules)
    {
        $this->rules = array();
        foreach ($rules as $ruleName => $rule) {
            $action = explode(':', $rule['action'], 2);
            if (count($action) !== 2 || !$action[0] || !$action[1]) {
                throw new \RuntimeException(
                    sprintf(
                        'Некоректно указано действие для правила раутинга "%s"',
                        $ruleName
                    )
                );
            }
            $this->rules[$ruleName] = array(
                'controller' => $action[0],
                'action'     => $action[1],
            );
            if (isset($rule['url'])) {
                $this->rules[$ruleName]['url'] = $rule['url'];
            }
        }
    }

    /**
     * @param string $url
     * @return array
     *      [ 'controller' => string , 'action' => string, 'params' => array ]
     * @throws \Minity\Http\NotFoundException
     */
    public function match($url)
    {
        foreach ($this->rules as $ruleName => $rule) {
            if (isset($rule['url']) && $rule['url'] == $url) {
                return $this->createResult($ruleName);
            }
        }
        throw new NotFoundException();
    }

    public function createResult($ruleName)
    {
        $rule = $this->rules[$ruleName];
        $rule['params'] = array();
        return $rule;
    }

    public function generate($ruleName, array $params = array())
    {
        if (!isset($this->rules[$ruleName])) {
            throw new \OutOfBoundsException(
                sprintf(
                    'Запрошено несуществующее правило с именем "%s"',
                    $ruleName
                )
            );
        }
        if (!isset($this->rules[$ruleName]['url'])) {
            throw new \OutOfRangeException(
                sprintf(
                    'Запрошено служебное правило с именем "%s"',
                    $ruleName
                )
            );
        }
        $paramString = http_build_query($params);
        return $this->rules[$ruleName]['url']
            . ($paramString ? '?' . $paramString : '');
    }

}
