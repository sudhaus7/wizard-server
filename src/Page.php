<?php

namespace Sudhaus7\WizardServer;

use React\Promise\Deferred;

class Page {

    protected int $pageid;

    public function __construct(int $pageid)
    {
        $this->pageid = $pageid;
    }

    public function fetch()
    {
        $deferred = new Deferred();
        $promise = $deferred->promise();

        $page = Database::getRecord( 'pages', $this->pageid);
        if (!isset($page['slug'])) {
            $real = Database::getRecord( 'tx_realurl_pathdata', $this->pageid,'page_id',false);
            if (!empty($real)) {
                $page['slug'] = '/'.trim($real['pagepath'],'/');
            }
        }
        $deferred->resolve( $page );
        return $promise;
    }
}
