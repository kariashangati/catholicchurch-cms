<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TranslationController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'locale' => (string) $request->input('locale', 'all'),
            'pair_status' => (string) $request->input('pair_status', 'all'),
            'per_page' => (int) $request->input('per_page', 25),
        ];

        if (! in_array($filters['locale'], ['all', 'en', 'sw'], true)) {
            $filters['locale'] = 'all';
        }

        if (! in_array($filters['pair_status'], ['all', 'complete', 'missing_en', 'missing_sw'], true)) {
            $filters['pair_status'] = 'all';
        }

        if (! in_array($filters['per_page'], [10, 25, 50, 100], true)) {
            $filters['per_page'] = 25;
        }

        $baseQuery = Translation::query()
            ->select([
                'translations.*',
                DB::raw("CASE WHEN EXISTS (SELECT 1 FROM translations t2 WHERE t2.translation_key = translations.translation_key AND t2.locale = 'en') THEN 1 ELSE 0 END AS has_en_pair"),
                DB::raw("CASE WHEN EXISTS (SELECT 1 FROM translations t3 WHERE t3.translation_key = translations.translation_key AND t3.locale = 'sw') THEN 1 ELSE 0 END AS has_sw_pair"),
            ]);

        $this->applyFilters($baseQuery, $filters);

        $filteredRows = (clone $baseQuery)->toBase()->getCountForPagination();

        $translations = $baseQuery
            ->orderBy('translation_key')
            ->orderBy('locale')
            ->paginate($filters['per_page'])
            ->withQueryString();

        $pairedStats = Translation::query()
            ->select('translation_key')
            ->selectRaw("MAX(CASE WHEN locale = 'en' THEN 1 ELSE 0 END) AS has_en")
            ->selectRaw("MAX(CASE WHEN locale = 'sw' THEN 1 ELSE 0 END) AS has_sw")
            ->groupBy('translation_key')
            ->get();

        $stats = [
            'total_rows' => Translation::query()->count(),
            'en_rows' => Translation::query()->where('locale', 'en')->count(),
            'sw_rows' => Translation::query()->where('locale', 'sw')->count(),
            'unique_keys' => Translation::query()->distinct('translation_key')->count('translation_key'),
            'complete_pairs' => $pairedStats->filter(fn ($row) => (int) $row->has_en === 1 && (int) $row->has_sw === 1)->count(),
            'missing_en' => $pairedStats->filter(fn ($row) => (int) $row->has_en === 0)->count(),
            'missing_sw' => $pairedStats->filter(fn ($row) => (int) $row->has_sw === 0)->count(),
            'filtered_rows' => $filteredRows,
        ];

        $bulkPreview = session('bulk_preview');

        return view('translations.index', compact('translations', 'filters', 'stats', 'bulkPreview'));
    }

    public function create()
    {
        return view('translations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(['en', 'sw'])],
            'translation_key' => ['required', 'string', 'max:191'],
            'translation_value' => ['required', 'string'],
        ]);

        Translation::query()->updateOrCreate(
            [
                'locale' => $validated['locale'],
                'translation_key' => $validated['translation_key'],
            ],
            [
                'translation_value' => $validated['translation_value'],
            ]
        );

        Cache::flush();

        return redirect()
            ->route('translations.index')
            ->with('success', db_trans('translation_created_successfully'));
    }

    public function edit(Translation $translation)
    {
        return view('translations.edit', compact('translation'));
    }

    public function update(Request $request, Translation $translation)
    {
        $validated = $request->validate([
            'translation_value' => ['required', 'string'],
        ]);

        $translation->update([
            'translation_value' => $validated['translation_value'],
        ]);

        Cache::flush();

        return redirect()
            ->route('translations.index')
            ->with('success', db_trans('translation_updated_successfully'));
    }

    public function destroy(Translation $translation)
    {
        $translation->delete();

        Cache::flush();

        return redirect()
            ->route('translations.index')
            ->with('success', db_trans('translation_deleted_successfully'));
    }

    public function bulkReplace(Request $request)
    {
        $validated = $request->validate([
            'search_word' => ['required', 'string', 'max:191'],
            'replace_word' => ['required', 'string', 'max:191'],
            'locale' => ['required', Rule::in(['all', 'en', 'sw'])],
            'match_scope' => ['required', Rule::in(['values_only', 'keys_only', 'keys_and_values'])],
            'bulk_action' => ['required', Rule::in(['preview', 'apply'])],
        ]);

        $query = $this->bulkReplaceQuery($validated);
        $affectedRows = (clone $query)->count();

        if ($affectedRows < 1) {
            return redirect()
                ->route('translations.index')
                ->withInput($validated)
                ->with('warning', db_trans('no_matching_translations_found'));
        }

        if ($validated['bulk_action'] === 'preview') {
            $previewRows = (clone $query)
                ->orderBy('translation_key')
                ->orderBy('locale')
                ->limit(25)
                ->get()
                ->map(fn (Translation $translation) => [
                    'id' => $translation->id,
                    'locale' => $translation->locale,
                    'translation_key' => $translation->translation_key,
                    'old_value' => $translation->translation_value,
                    'new_value' => str_ireplace($validated['search_word'], $validated['replace_word'], $translation->translation_value),
                ])
                ->values()
                ->all();

            return redirect()
                ->route('translations.index', [
                    'search' => $validated['search_word'],
                    'locale' => $validated['locale'],
                ])
                ->withInput($validated)
                ->with('bulk_preview', [
                    'affected_rows' => $affectedRows,
                    'rows' => $previewRows,
                    'payload' => $validated,
                ]);
        }

        $updatedRows = 0;

        (clone $query)
            ->orderBy('id')
            ->chunkById(200, function ($translations) use ($validated, &$updatedRows) {
                foreach ($translations as $translation) {
                    $newValue = str_ireplace($validated['search_word'], $validated['replace_word'], $translation->translation_value);

                    if ($newValue === $translation->translation_value) {
                        continue;
                    }

                    $translation->update(['translation_value' => $newValue]);
                    $updatedRows++;
                }
            });

        Cache::flush();

        return redirect()
            ->route('translations.index', [
                'search' => $validated['replace_word'],
                'locale' => $validated['locale'],
            ])
            ->with('success', db_trans('replacement_applied_successfully') . ' (' . number_format($updatedRows) . ')');
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function (Builder $query) use ($search) {
                $query->where('translation_key', 'like', '%' . $search . '%')
                    ->orWhere('translation_value', 'like', '%' . $search . '%');
            });
        }

        if (in_array($filters['locale'], ['en', 'sw'], true)) {
            $query->where('locale', $filters['locale']);
        }

        if ($filters['pair_status'] === 'complete') {
            $query->whereRaw("EXISTS (SELECT 1 FROM translations t2 WHERE t2.translation_key = translations.translation_key AND t2.locale = 'en')")
                ->whereRaw("EXISTS (SELECT 1 FROM translations t3 WHERE t3.translation_key = translations.translation_key AND t3.locale = 'sw')");
        } elseif ($filters['pair_status'] === 'missing_en') {
            $query->whereRaw("NOT EXISTS (SELECT 1 FROM translations t2 WHERE t2.translation_key = translations.translation_key AND t2.locale = 'en')");
        } elseif ($filters['pair_status'] === 'missing_sw') {
            $query->whereRaw("NOT EXISTS (SELECT 1 FROM translations t3 WHERE t3.translation_key = translations.translation_key AND t3.locale = 'sw')");
        }
    }

    protected function bulkReplaceQuery(array $payload): Builder
    {
        $search = $payload['search_word'];

        return Translation::query()
            ->when(in_array($payload['locale'], ['en', 'sw'], true), fn (Builder $query) => $query->where('locale', $payload['locale']))
            ->where(function (Builder $query) use ($payload, $search) {
                if (in_array($payload['match_scope'], ['values_only', 'keys_and_values'], true)) {
                    $query->orWhere('translation_value', 'like', '%' . $search . '%');
                }

                if (in_array($payload['match_scope'], ['keys_only', 'keys_and_values'], true)) {
                    $query->orWhere('translation_key', 'like', '%' . $search . '%');
                }
            });
    }
}
