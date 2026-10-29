<?php

namespace App\Support;

use Throwable;

class ErrorLayout
{
    /**
     * Layout untuk halaman error. Navbar dan footer membaca database; bila error itu sendiri berasal
     * dari database (mis. 500), halaman error memakai layout ringkas agar tetap tampil.
     */
    public static function name(): string
    {
        try {
            PageContents::for('contact')->image('company_logo');

            return 'layouts.app';
        } catch (Throwable) {
            return 'layouts.error';
        }
    }
}
