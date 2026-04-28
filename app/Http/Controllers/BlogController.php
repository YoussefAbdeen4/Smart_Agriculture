<?php

namespace App\Http\Controllers;

use App\Http\Resources\AllBlogsResource;
use App\Http\Resources\BlogResource;
use App\Http\Traits\ApiTrait;
use App\Http\Traits\media;
use App\Models\AttachmentBlog;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\React;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    use ApiTrait, AuthorizesRequests, media;

    /**
     * Display a listing of blogs.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $blogs = Blog::with(['user', 'comments' => function ($query) {
            $query->with('user')->latest();
        }, 'reactions' => function ($query) {
            $query->with('user');
        }, 'attachments'])
            ->latest()
            ->paginate(15);

        return AllBlogsResource::collection($blogs)->additional([
            'message' => 'Blogs retrieved successfully',
            'errors'  => (object)[],
        ]);
    }

    /**
     * Store a newly created blog in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Blog::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,mp4,mov,avi', 'max:20480'],
        ]);

        $blog = Blog::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'user_id' => $request->user()->id,
        ]);

        // Process attachments if provided
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Store the file
                $fileName = $this->uploadPhoto($file, 'blogs/attachments');
                // Create attachment record
                $blog->attachments()->create([
                    'name' => $fileName,
                ]);
            }
        }
        $blog->user = $request->user();
        // Reload blog with attachments
        $blog->load('attachments');

        return $this->dataResponse(
            new BlogResource($blog),
            'Blog created successfully',
            201
        );
    }

    /**
     * Display the specified blog.
     */
    public function show(Request $request, Blog $blog): JsonResponse
    {
        $this->authorize('view', $blog);

        $blog->load(['user', 'comments' => function ($query) {
            $query->with('user')->latest();
        }, 'reactions' => function ($query) {
            $query->with('user');
        }, 'attachments']);
//dd($blog);
        return $this->dataResponse(
            new AllBlogsResource($blog),
            'Blog retrieved successfully'
        );;
    }

    /**
     * Update the specified blog in storage.
     */
    public function update(Request $request, Blog $blog): JsonResponse
    {
        $this->authorize('update', $blog);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
        ]);

        // Update blog content
        $blog->update([
            'title' => $validated['title'] ?? $blog->title,
            'content' => $validated['content'] ?? $blog->content,
        ]);
        // Reload blog with attachments
        $blog->load(['user', 'comments' => function ($query) {
            $query->with('user');
        }, 'reactions' => function ($query) {
            $query->with('user');
        }, 'attachments']);

        return $this->dataResponse(
            new AllBlogsResource($blog),
            'Blog updated successfully'
        );
    }

    /**
     * Remove the specified blog from storage.
     */
    public function destroy(Request $request, Blog $blog): JsonResponse
    {
        $this->authorize('delete', $blog);

        $files = AttachmentBlog::where('blog_id',$blog->id)->get();
        if($files){
            foreach($files as $file){
                $path = public_path('img/blogs/attachments/' . $file->name);
                $this->deletePhoto($path);
            }
        }

        $blog->delete();

        return $this->successResponse('Blog deleted successfully');
    }

    /**
     * Add a comment to the blog.
     */
    public function addComment(Request $request, Blog $blog): JsonResponse
    {
        $this->authorize('comment', $blog);

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $comment = Comment::create([
            'content' => $validated['content'],
            'blog_id' => $blog->id,
            'user_id' => $request->user()->id,
        ]);

        $comment->load('user');

        return $this->dataResponse(
            compact('comment'),
            'Comment added successfully',
            201
        );
    }

    /**
     * Toggle a reaction (like) on the blog.
     */
    public function toggleReaction(Request $request, Blog $blog): JsonResponse
    {
        $this->authorize('react', $blog);

        $validated = $request->validate([
            'is_like' => ['required', 'boolean'],
        ]);

        $reaction = React::where('blog_id', $blog->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($reaction) {
            $reaction->delete();

            return $this->successResponse('Reaction removed successfully');
        }

        $reaction = React::create([
            'is_like' => $validated['is_like'],
            'blog_id' => $blog->id,
            'user_id' => $request->user()->id,
        ]);

        return $this->dataResponse(
            compact('reaction'),
            'Reaction added successfully',
            201
        );
    }

}
