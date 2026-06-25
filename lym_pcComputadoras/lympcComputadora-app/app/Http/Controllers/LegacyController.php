<?php

namespace App\Http\Controllers;

use App\Support\Legacy\LegacyRuntime;
use Illuminate\Http\Response;

class LegacyController extends Controller
{
    public function __invoke(string $script, LegacyRuntime $runtime): Response
    {
        return $runtime->run($script);
    }
}
