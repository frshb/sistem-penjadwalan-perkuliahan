<?php
// app/Helpers/FilterPersister.php

namespace App\Helpers;

use Illuminate\Http\Request;

class FilterPersister
{
    /**
     * Handle persisting and restoring filter values.
     * Returns an array with action ('redirect' or 'none') and parameters/filters.
     */
    public static function handle(Request $request, string $routeName, array $possibleFilterKeys): array
    {
        $moduleKey = 'filter_' . $routeName;

        // 1. Reset filter
        if ($request->has('reset') || $request->has('clear')) {
            $request->session()->forget($moduleKey);
            
            // Keep required query parameters like 'tahun' for Kelas
            $params = [];
            if ($request->has('tahun')) {
                $params['tahun'] = $request->input('tahun');
            }
            return ['action' => 'redirect', 'route' => $routeName, 'params' => $params];
        }

        // 2. Check if request has any filter keys
        $hasAnyFilterKey = false;
        foreach ($possibleFilterKeys as $key) {
            if ($request->has($key)) {
                $hasAnyFilterKey = true;
                break;
            }
        }

        if ($hasAnyFilterKey) {
            $filters = [];
            foreach ($possibleFilterKeys as $key) {
                if ($request->has($key)) {
                    $filters[$key] = $request->input($key);
                }
            }
            $request->session()->put($moduleKey, $filters);
            return ['action' => 'none', 'filters' => $filters];
        } else {
            if ($request->session()->has($moduleKey)) {
                $filters = $request->session()->get($moduleKey);
                // Also retain other required parameters in the redirect
                if ($request->has('tahun')) {
                    $filters['tahun'] = $request->input('tahun');
                }
                return ['action' => 'redirect', 'route' => $routeName, 'params' => $filters];
            }
        }

        return ['action' => 'none', 'filters' => []];
    }
}
