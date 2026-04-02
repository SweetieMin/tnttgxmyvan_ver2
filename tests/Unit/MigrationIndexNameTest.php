<?php

use Illuminate\Support\Collection;

test('migration index names stay within mysql limits', function () {
    $migrationDirectory = dirname(__DIR__, 2).'/database/migrations';

    $violations = collect(glob($migrationDirectory.'/*.php'))
        ->flatMap(function (string $migrationPath): Collection {
            $source = file_get_contents($migrationPath);

            if ($source === false) {
                return collect();
            }

            preg_match("/Schema::create\\('([^']+)'/", $source, $tableMatches);

            $tableName = $tableMatches[1] ?? null;

            if ($tableName === null) {
                return collect();
            }

            preg_match_all(
                "/->(unique|index)\\(\\s*(\\[[^\\)]*\\]|'[^']+')\\s*(?:,\\s*'([^']+)')?\\s*\\)/s",
                $source,
                $indexMatches,
                PREG_SET_ORDER
            );

            return collect($indexMatches)
                ->map(function (array $match) use ($migrationPath, $tableName): ?array {
                    $indexType = $match[1];
                    $columnArgument = $match[2];
                    $explicitName = $match[3] ?? null;

                    preg_match_all("/'([^']+)'/", $columnArgument, $columnMatches);

                    $columns = $columnMatches[1];

                    if ($columns === []) {
                        return null;
                    }

                    $indexName = $explicitName
                        ?? "{$tableName}_".implode('_', $columns)."_{$indexType}";

                    return [
                        'migration' => basename($migrationPath),
                        'index' => $indexName,
                        'length' => strlen($indexName),
                    ];
                })
                ->filter(fn (?array $index): bool => $index !== null && $index['length'] > 64)
                ->values();
        })
        ->values()
        ->all();

    expect($violations)->toBeEmpty();
});
