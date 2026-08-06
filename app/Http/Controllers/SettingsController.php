<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        return view('waste_management.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($request->except(['_token','_method']) as $key => $value) {
            Setting::set($key, $value);
        }
        return redirect()->route('settings.index')->with('success', __('Settings saved successfully'));
    }
}