<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const DEFAULTS = [
        'site_name' => 'Laravel Login System',
        'support_email' => 'support@example.com',
        'timezone' => 'UTC',
    ];

    public function index(): View
    {
        $settings = collect(self::DEFAULTS)
            ->map(fn ($default, $key) => Setting::get($key, $default));

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'support_email' => ['required', 'email', 'max:255'],
            'timezone' => ['required', 'string', 'max:64'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        Activity::log('updated', null, 'Updated application settings');

        return redirect()->route('settings.index')->with('status', 'Settings updated.');
    }
}
