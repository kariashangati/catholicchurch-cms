<?php

namespace App\Http\Controllers\Admin\SystemConfig;

use App\Http\Controllers\Controller;
use App\Services\SystemConfig\SystemInformationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SystemInformationController extends Controller
{
    public function __construct(
        protected SystemInformationService $systemInformationService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('system.config.systeminfo.view'), 403);

        return view('admin.system-config.system-information', [
            'info' => $this->systemInformationService->data(),
        ]);
    }
}