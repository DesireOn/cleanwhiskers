<?php
declare(strict_types=1);

// usage: php tools/coverage-diff-check.php build/logs/clover.xml BASE_BRANCH MIN_PERCENT

[$script, $cloverPath, $baseBranch, $minPercent] = array_pad($argv, 4, null);

if (!$cloverPath || !$baseBranch || !$minPercent) {
    fwrite(STDERR, "Usage: php tools/coverage-diff-check.php <clover.xml> <base-branch> <min-percent>\n");
    exit(2);
}

if (!is_file($cloverPath)) {
    fwrite(STDERR, "Clover file not found: {$cloverPath}\n");
    exit(2);
}

// Ensure base branch is available for diff (GitHub Actions checks out a detached HEAD)
// If fetch fails, continue; we'll fallback to overall coverage if we cannot compute a diff
@exec(sprintf('git fetch --no-tags --prune --depth=1 origin %s 2>&1', escapeshellarg($baseBranch)), $outFetch, $rcFetch);

// Restrict to changed PHP files under src/
$cmd = sprintf('git diff --name-only origin/%s...HEAD -- "src/**/*.php" 2>&1', escapeshellarg($baseBranch));
@exec($cmd, $changedFilesRaw, $rcDiff);

$changedFiles = array_values(array_filter(array_map('trim', $changedFilesRaw), static function ($f) {
    return $f !== '' && str_ends_with($f, '.php');
}));

// Load clover
$xml = @simplexml_load_file($cloverPath);
if ($xml === false) {
    fwrite(STDERR, "Failed to parse clover XML: {$cloverPath}\n");
    exit(2);
}

// Build a fast lookup for file coverage from clover
$filesCoverage = [];
foreach ($xml->xpath('//file') as $fileNode) {
    /** @var SimpleXMLElement $fileNode */
    $path = (string) $fileNode['name'];
    $metrics = $fileNode->metrics;
    if (!$metrics) {
        continue;
    }
    $statements = (int) $metrics['statements'];
    $covered = (int) $metrics['coveredstatements'];
    $filesCoverage[$path] = [$statements, $covered];
}

// Helper: normalize paths to match clover file names and git diff outputs
$normalize = static function (string $p): string {
    $p = str_replace('\\', '/', $p);
    // If absolute path, trim up to repo-relative src/
    $pos = strpos($p, '/src/');
    if ($pos !== false) {
        $p = substr($p, $pos + 1); // keep from 'src/...'
    }
    // remove leading ./
    $p = preg_replace('#^\./#', '', $p);
    return $p;
};

// If we have no changed PHP files or git diff failed, fall back to overall coverage
// We'll further filter out controllers from the changed set before deciding.
$useOverall = ($rcDiff !== 0) || count($changedFiles) === 0;

$totalStatements = 0;
$totalCovered = 0;

if ($useOverall) {
    // Sum all files under src/, excluding src/Entity
    foreach ($filesCoverage as $path => [$s, $c]) {
        $np = $normalize($path);
        if (str_starts_with($np, 'src/') && !str_starts_with($np, 'src/Entity/')) {
            $totalStatements += $s;
            $totalCovered += $c;
        }
    }
    $scope = 'overall src/ (excluding src/Entity)';
} else {
    // Only consider changed files found in clover, excluding src/Controller/* and src/Entity/*
    $normalizedChanged = array_map($normalize, $changedFiles);
    $normalizedChanged = array_values(array_filter($normalizedChanged, static function (string $p): bool {
        return !str_starts_with($p, 'src/Controller/') && !str_starts_with($p, 'src/Entity/');
    }));

    // If no non-controller/entity files remain, treat as nothing to check
    if (count($normalizedChanged) === 0) {
        $scope = 'changed PHP files (no non-controller/entity changes)';
    } else {
        $changedSet = array_flip($normalizedChanged);
        foreach ($filesCoverage as $path => [$s, $c]) {
            $np = $normalize($path);
            if (isset($changedSet[$np])) {
                $totalStatements += $s;
                $totalCovered += $c;
            }
        }
        $scope = 'changed PHP files (excluding src/Controller/* and src/Entity/*)';
    }
}

// If no statements found (e.g., files without executable lines), treat as fully covered
if ($totalStatements === 0) {
    fwrite(STDOUT, "No executable statements found in {$scope}; treating as 100% covered.\n");
    exit(0);
}

$pct = $totalCovered / $totalStatements * 100.0;
$pctStr = number_format($pct, 2);

$min = (float) $minPercent;

fwrite(STDOUT, sprintf("Coverage (%s): %s%% (min %s%%)\n", $scope, $pctStr, $min));

if ($pct + 1e-9 < $min) {
    fwrite(STDERR, sprintf("::error::Coverage is below threshold: %s%% < %s%% (%s)\n", $pctStr, $min, $scope));
    exit(1);
}

exit(0);
