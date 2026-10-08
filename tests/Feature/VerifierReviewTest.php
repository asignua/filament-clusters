<?php

declare(strict_types=1);

namespace Asignua\FilamentClusters\Tests\Feature;

use Asignua\FilamentClusters\Cluster;
use Asignua\FilamentClusters\Tests\TestCase;
use Filament\Forms\Components\TextInput;
use Livewire\Livewire;
use Workbench\App\Livewire\SingleClusterForm;

/**
 * Verifier pass (2026-10-08) after review round 1. `test_bug_` tests document a defect and are EXPECTED TO FAIL
 * until the production code is fixed.
 */
class VerifierReviewTest extends TestCase
{
    public function test_bug_describe_child_does_not_corrupt_an_attribute_that_contains_a_greater_than_sign(): void
    {
        SingleClusterForm::$components = [
            Cluster::make([
                TextInput::make('a')->extraInputAttributes(['x-on:input' => 'if ($el.value.length > 3) done = true']),
                TextInput::make('b'),
            ])->label('Pair')->helperText('Some help'),
        ];

        $html = Livewire::test(SingleClusterForm::class)->html();

        // describeChild() matches `<input …>` with `[^>]*>`, so the tag is cut at the `>` inside the Alpine
        // expression and aria-describedby is spliced INTO the attribute value: broken JS + stray visible text.
        $this->assertMatchesRegularExpression('/x-on:input="if \\(\\$el\\.value\\.length (>|&gt;) 3\\) done = true"/', $html);
        $this->assertStringNotContainsString('length  aria-describedby', $html);
        $this->assertStringNotContainsString('length aria-describedby', $html);
    }
}
