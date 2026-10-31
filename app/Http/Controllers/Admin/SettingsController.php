<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', ['groups' => SiteSettings::groups()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $fields = SiteSettings::fields();

        // Form inputs use "__" in place of "." (e.g. contact__email), since dots
        // in input names are treated as nested arrays.
        $rules = collect($fields)->mapWithKeys(fn ($field, $key) => [self::inputName($key) => $field['rules']])->all();
        $labels = collect($fields)->mapWithKeys(fn ($field, $key) => [self::inputName($key) => strtolower($field['label'])])->all();

        $validated = $request->validate($rules, [], $labels);

        Setting::store(collect($validated)->mapWithKeys(fn ($value, $input) => [str_replace('__', '.', $input) => $value])->all());

        return back()->with('status', 'Settings saved. The website now uses the updated details.');
    }

    public static function inputName(string $key): string
    {
        return str_replace('.', '__', $key);
    }
}
