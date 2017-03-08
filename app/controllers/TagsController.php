<?php

namespace Kitsune\Controllers;

use Phalcon\Cache\BackendInterface;
use Phalcon\Mvc\Controller as PhController;

/**
 * Class DocsController
 *
 * @package Docs\Controllers
 *
 * @property BackendInterface $cacheData
 * @property \ParsedownExtra  $parsedown
 */
class TagsController extends PhController
{
    public function mainAction($page = '')
    {
        $page = ($page) ?: 'index';

        $contents = $this->viewSimple->render(
            'posts/index',
            [
                'sidebar'  => $this->getDocument($language, 'sidebar'),
                'article'  => $this->getDocument($language, $page),
                'menu'     => $this->getDocument($language, $page . '-menu'),
            ]
        );
        $this->response->setContent($contents);

        return $this->response;

    }

    public function postAction($post)
    {
        echo 'posts action';
    }

    /**
     * @param string $language
     * @param string $fileName
     *
     * @return string
     */
    private function getDocument($language, $fileName)
    {
        $key = sprintf('%s.%s.cache', $fileName,$language);
        if ('production' === $this->config->get('app')->get('env') &&
            true === $this->cacheData->exists($key)) {
            return $this->cacheData->get($key);
        } else {
            $fileName = sprintf(
                '%s/docs/%s/%s.md',
                APP_PATH,
                $language,
                $fileName
            );

            if (file_exists($fileName)) {
                $data = file_get_contents($fileName);
                $data = str_replace(
                    '[[version]]',
                    $this->getVersion(),
                    $data
                );
                $data = $this->parsedown->text($data);
                $this->cacheData->save($key, $data);
            } else {
                $data = '';
            }

            return $data;
        }
    }

    /**
     * Check the available languages and return either that or 'en'
     *
     * @param string $language
     *
     * @return string
     */
    private function getLanguage($language)
    {
       $languages = $this->config->get('languages', ['en' => 'English']);

       if (!array_key_exists($language, $languages)) {
           return 'en';
       } else {
           return $language;
       }
    }

    private function getVersion()
    {
        return '3.0.4';
    }
}
