<?php

namespace App\Helper;


use Symfony\Component\HttpFoundation\Response;

class ViewHelper
{
    /**
     * @param array $scripts
     *
     * @return Response
     */
    public static function getResponse(array $scripts): Response
    {
        $scripts[] = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js';
        $scripts[] = '/js/lib.js';
        $scripts[] = '/js/libs/jquery-ui-1.9.2.custom.js';
        $scripts[] = '/js/libs/jquery.multiselect.js';

        $css = [
            '/css/jquery.multiselect.css'
        ];

        $html = '<html>
            <head>
                <title></title>
                {css}
                {scripts}
            <body> 
                <script>
                    $(document).ready(function() {
                        init();
                    });
                </script>
            </body>
        </head>
        </html>';
        $scriptTemplate = '<script src="{path}"></script>';
        $cssTemplate = '<link rel = "stylesheet" type = "text/css" href = "{path}"/>';

        $templateParams = ['scripts' => [], 'css' => []];
        foreach ($scripts as $script) {
            $templateParams['scripts'][] = str_replace('{path}', $script, $scriptTemplate);
        }
        foreach ($css as $item) {
            $templateParams['css'][] = str_replace('{path}', $item, $cssTemplate);
        }

        $html = str_replace('{css}', implode('', $templateParams['css']), $html);

        return new Response(str_replace('{scripts}', implode('', $templateParams['scripts']), $html));
    }
}