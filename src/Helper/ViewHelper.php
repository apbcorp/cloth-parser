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

        $html = '<html>
            <head>
                <title></title>
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

        $templateParams = ['scripts' => []];
        foreach ($scripts as $script) {
            $templateParams['scripts'][] = str_replace('{path}', $script, $scriptTemplate);
        }

        return new Response(str_replace('{scripts}', implode('', $templateParams['scripts']), $html));
    }
}