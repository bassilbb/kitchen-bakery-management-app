<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
        ]);

        if (isset($data['company_name'])) {
            Setting::set(Setting::COMPANY_NAME_KEY, trim($data['company_name']));
        }

        if ($request->hasFile('logo')) {
            $old = Setting::get(Setting::LOGO_PATH_KEY);
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            $path = $request->file('logo')->store('logos', 'public');
            Setting::set(Setting::LOGO_PATH_KEY, $path);
        }

        return back()->with('success', 'Company settings updated.');
    }
}
