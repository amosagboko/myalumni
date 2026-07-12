<?php

namespace App\Support\Social;

use Illuminate\Support\Collection;

class CommentTreeBuilder
{
    /**
     * Build a nested comment tree from a flat collection.
     * Top-level nodes are newest-first; children within each branch are oldest-first.
     */
    public function build(Collection $comments): Collection
    {
        if ($comments->isEmpty()) {
            return collect();
        }

        $byParent = $comments->groupBy(fn ($comment) => $comment->parent_id ?? 0);

        $attachChildren = function ($comment) use (&$attachChildren, $byParent) {
            $children = $byParent->get($comment->id, collect())
                ->sortBy('created_at')
                ->values();

            $comment->setRelation(
                'children',
                $children->map(fn ($child) => $attachChildren($child))
            );

            return $comment;
        };

        return $byParent->get(0, collect())
            ->sortByDesc('created_at')
            ->values()
            ->map(fn ($comment) => $attachChildren($comment));
    }
}
