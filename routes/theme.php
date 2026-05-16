<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/admin/theme', function (Request $request) {
        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:classic,light,dark'],
        ]);

        $user = $request->user();
        $theme = $validated['theme'];

        if ($user && $user->admin_theme !== $theme) {
            $user->forceFill([
                'admin_theme' => $theme,
            ])->save();
        }

        session(['admin_theme' => $theme]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'theme' => $theme,
                'message' => db_trans('theme_updated_successfully'),
            ]);
        }

        return back()->with('success', db_trans('theme_updated_successfully'));
    })->name('admin.theme.update');
});
