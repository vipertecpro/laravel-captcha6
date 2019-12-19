<?php

namespace Vipertecpro\Captcha\Captcha\Storage;

use Illuminate\Http\Request;
use Illuminate\Session\Store;

class SessionStorage implements StorageInterface
{
    /**
     * @var Store
     */
    private $session;

    /**
     * @var string
     */
    private $key = 'bone_captcha';

    /**
     * SessionStorage constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->session = $request->session();
    }

    /**
     * @inheritdoc
     */
    public function push($code):void
    {
        $this->session->put($this->key, $code);
    }

    /**
     * @inheritdoc
     */
    public function pull()
    {
        $code = $this->session->get($this->key);
        if (! empty($code)) {
            $this->session->forget($this->key);
        }

        return $code;
    }
}
