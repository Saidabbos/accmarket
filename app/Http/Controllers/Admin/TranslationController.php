<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TranslationController extends Controller
{
    public function index(Request $request)
    {
        $languages = Language::active()->ordered()->get();
        $currentLocale = $request->get('locale', $languages->first()?->code ?? 'en');
        $currentGroup = $request->get('group', '');
        $search = $request->get('search', '');

        // Get all unique groups
        $groups = Translation::select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group');

        // Get translations for current locale
        $query = Translation::where('locale', $currentLocale);

        if ($currentGroup) {
            $query->where('group', $currentGroup);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%");
            });
        }

        $translations = $query->orderBy('group')->orderBy('key')->paginate(50);

        return Inertia::render('Admin/Translations/Index', [
            'languages' => $languages,
            'groups' => $groups,
            'translations' => $translations,
            'filters' => [
                'locale' => $currentLocale,
                'group' => $currentGroup,
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'locale' => 'required|exists:languages,code',
            'group' => 'required|string|max:255',
            'key' => 'required|string|max:255',
            'value' => 'required|string',
        ]);

        Translation::updateOrCreate(
            [
                'locale' => $request->locale,
                'group' => $request->group,
                'key' => $request->key,
            ],
            ['value' => $request->value]
        );

        Translation::clearCache($request->locale);

        return back()->with('success', 'Translation created successfully.');
    }

    public function update(Request $request, Translation $translation)
    {
        $request->validate([
            'value' => 'required|string',
        ]);

        $translation->update(['value' => $request->value]);

        Translation::clearCache($translation->locale);

        return back()->with('success', 'Translation updated successfully.');
    }

    public function destroy(Translation $translation)
    {
        $locale = $translation->locale;
        $translation->delete();

        Translation::clearCache($locale);

        return back()->with('success', 'Translation deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'locale' => 'required|exists:languages,code',
        ]);

        $count = Translation::importFromFile($request->locale);

        return back()->with('success', "Imported {$count} translations.");
    }

    public function importAll()
    {
        $languages = Language::all();
        $totalCount = 0;

        foreach ($languages as $language) {
            $count = Translation::importFromFile($language->code);
            $totalCount += $count;
        }

        return back()->with('success', "Imported {$totalCount} translations for all languages.");
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'translations' => 'required|array',
            'translations.*.id' => 'required|exists:translations,id',
            'translations.*.value' => 'required|string',
        ]);

        $locales = [];

        foreach ($request->translations as $item) {
            $translation = Translation::find($item['id']);
            if ($translation) {
                $translation->update(['value' => $item['value']]);
                $locales[$translation->locale] = true;
            }
        }

        // Clear cache for all affected locales
        foreach (array_keys($locales) as $locale) {
            Translation::clearCache($locale);
        }

        return back()->with('success', 'Translations updated successfully.');
    }
}
