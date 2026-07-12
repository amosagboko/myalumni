<?php

namespace Tests\Unit\Social;

use App\Models\Comment;
use App\Support\Social\CommentTreeBuilder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CommentTreeBuilderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_builds_tree_with_newest_top_level_and_oldest_children(): void
    {
        $rootOld = tap(new Comment, function (Comment $comment) {
            $comment->forceFill([
                'id' => 1,
                'parent_id' => null,
                'comment' => 'Root old',
                'created_at' => Carbon::parse('2026-01-01 10:00:00'),
            ]);
        });
        $rootNew = tap(new Comment, function (Comment $comment) {
            $comment->forceFill([
                'id' => 2,
                'parent_id' => null,
                'comment' => 'Root new',
                'created_at' => Carbon::parse('2026-01-02 10:00:00'),
            ]);
        });
        $childOld = tap(new Comment, function (Comment $comment) {
            $comment->forceFill([
                'id' => 3,
                'parent_id' => 1,
                'comment' => 'Child old',
                'created_at' => Carbon::parse('2026-01-01 11:00:00'),
            ]);
        });
        $childNew = tap(new Comment, function (Comment $comment) {
            $comment->forceFill([
                'id' => 4,
                'parent_id' => 1,
                'comment' => 'Child new',
                'created_at' => Carbon::parse('2026-01-01 12:00:00'),
            ]);
        });

        $tree = app(CommentTreeBuilder::class)->build(collect([
            $rootOld,
            $rootNew,
            $childOld,
            $childNew,
        ]));

        $this->assertCount(2, $tree);
        $this->assertSame(2, $tree->first()->id);
        $this->assertSame(1, $tree->last()->id);

        $children = $tree->last()->children;
        $this->assertCount(2, $children);
        $this->assertSame(3, $children->first()->id);
        $this->assertSame(4, $children->last()->id);
    }
}
