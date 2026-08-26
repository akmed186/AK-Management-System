<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Complaint;
use App\Models\ComplaintComment;
use App\Notifications\ComplaintCommentAdded;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ComplaintCommentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $comments = $this->filteredQuery($search)
            ->paginate(10)
            ->withQueryString();

        return view('complaint-comments.index', compact('comments', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $comments = $this->filteredQuery($search)->get();

        $headers = ['Complaint', 'By', 'Comment', 'Posted'];
        $rows = $comments->map(fn (ComplaintComment $comment) => [
            $comment->complaint->title,
            $comment->user?->name ?? 'Deleted user',
            Str::limit($comment->comment_text, 120),
            $comment->created_at->format('M j, Y'),
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('complaint-comments.pdf', 'Complaint Comments', $headers, $rows)
            : Exporter::csv('complaint-comments.csv', $headers, $rows);
    }

    private function filteredQuery(string $search)
    {
        return ComplaintComment::with(['complaint', 'user'])
            ->when($search, function ($query, $search) {
                $query->where('comment_text', 'like', "%{$search}%")
                    ->orWhereHas('complaint', function ($query) use ($search) {
                        $query->where('title', 'like', "%{$search}%");
                    });
            })
            ->latest();
    }

    public function create(): View
    {
        $complaints = Complaint::orderByDesc('id')->get();

        return view('complaint-comments.create', compact('complaints'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'complaint_id' => ['required', 'exists:complaints,id'],
            'comment_text' => ['required', 'string'],
        ]);

        $validated['user_id'] = auth()->id();

        $comment = ComplaintComment::create($validated);

        Activity::log('created', $comment, 'Commented on complaint "'.$comment->complaint->title.'"');

        if ($comment->complaint->tenant->user) {
            try {
                $comment->complaint->tenant->user->notify(new ComplaintCommentAdded($comment));
            } catch (\Throwable $e) {
                Log::warning('Failed to notify tenant of new complaint comment', ['comment_id' => $comment->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('complaint-comments.index')->with('status', 'Comment added.');
    }

    public function edit(ComplaintComment $complaintComment): View
    {
        $complaints = Complaint::orderByDesc('id')->get();

        return view('complaint-comments.edit', ['comment' => $complaintComment, 'complaints' => $complaints]);
    }

    public function update(Request $request, ComplaintComment $complaintComment): RedirectResponse
    {
        $validated = $request->validate([
            'complaint_id' => ['required', 'exists:complaints,id'],
            'comment_text' => ['required', 'string'],
        ]);

        $complaintComment->update($validated);

        Activity::log('updated', $complaintComment, 'Updated comment on complaint "'.$complaintComment->complaint->title.'"');

        return redirect()->route('complaint-comments.index')->with('status', 'Comment updated.');
    }

    public function destroy(ComplaintComment $complaintComment): RedirectResponse
    {
        Activity::log('deleted', null, 'Deleted comment on complaint "'.$complaintComment->complaint->title.'"');

        $complaintComment->delete();

        return redirect()->route('complaint-comments.index')->with('status', 'Comment deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:complaint_comments,id'],
        ]);

        Activity::log('deleted', null, 'Bulk deleted '.count($validated['ids']).' complaint comments');

        ComplaintComment::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('complaint-comments.index')->with('status', 'Comments deleted.');
    }
}
