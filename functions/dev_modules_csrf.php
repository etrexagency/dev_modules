<?php
class rex_api_dev_modules_go extends rex_api_function
{
    protected $published = false; // nur im Backend

    public function execute(): never
    {
        if (!rex::getUser()) {
            rex_response::setStatus(403);
            exit('Forbidden');
        }

        $do        = rex_request('do', 'string'); // 'move' | 'status'
        $articleId = rex_request('article_id', 'int');
        $sliceId   = rex_request('slice_id', 'int');
        $clang     = rex_request('clang', 'int', 1);
        $ctype     = rex_request('ctype', 'int', 1);

        if (!$articleId || !$sliceId || !in_array($do, ['move', 'status'], true)) {
            rex_response::setStatus(400);
            exit('Bad Request');
        }

        $params = [
            'page'       => 'content/edit',
            'article_id' => $articleId,
            'slice_id'   => $sliceId,
            'clang'      => $clang,
            'ctype'      => $ctype,
        ];

        if ($do === 'move') {
            $direction = rex_request('direction', 'string', 'moveup');
            if (!in_array($direction, ['moveup', 'movedown'], true)) {
                rex_response::setStatus(400);
                exit('Bad Request (direction)');
            }
            $params += [
                'upd'          => time(),
                'direction'    => $direction,
                'rex-api-call' => 'content_move_slice',
            ];
            $params += rex_csrf_token::factory(rex_api_content_move_slice::class)->getUrlParams();
        } else {
            $status = rex_request('status', 'int') ? 1 : 0;
            $params += [
                'status'       => $status,
                'rex-api-call' => 'content_slice_status',
            ];
            $params += rex_csrf_token::factory(rex_api_content_slice_status::class)->getUrlParams();
        }

        // Ziel-URL sauber vom Core bauen lassen
        $url = rex_url::backendController($params);
        // replace &ump; with &
        $url = str_replace('&amp;', '&', $url);
        $url .= '#slice' . $sliceId;

        rex_response::sendRedirect($url);
        exit;
    }
}
