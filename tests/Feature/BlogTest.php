<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Test user can create a blog without attachments.
     */
    public function test_user_can_create_blog_without_attachments(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/blogs', [
            'title' => 'My First Blog',
            'content' => 'This is the blog content.',
        ]);

        $response->assertCreated()
            ->assertJson([
                'message' => 'Blog created successfully',
                'data' => [
                    'blog' => [
                        'title' => 'My First Blog',
                        'content' => 'This is the blog content.',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('blog', [
            'title' => 'My First Blog',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test user can create a blog with image attachments.
     */
    public function test_user_can_create_blog_with_image_attachments(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/blogs', [
            'title' => 'Blog with Images',
            'content' => 'Content with images',
            'attachments' => [
                UploadedFile::fake()->create('photo1.jpg', 500, 'image/jpeg'),
                UploadedFile::fake()->create('photo2.png', 500, 'image/png'),
            ],
        ]);

        $response->assertCreated()
            ->assertJson([
                'message' => 'Blog created successfully',
            ]);

        $blog = Blog::where('title', 'Blog with Images')->first();
        $this->assertNotNull($blog);
        $this->assertCount(2, $blog->attachments);

        // Verify attachments
        $attachments = $blog->attachments()->get();
        $this->assertTrue($attachments->every(fn ($att) => $att->file_type === 'image'));
        $this->assertTrue($attachments->every(fn ($att) => $att->file_path !== null));
    }

    /**
     * Test user can create a blog with video attachments.
     */
    public function test_user_can_create_blog_with_video_attachments(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/blogs', [
            'title' => 'Blog with Videos',
            'content' => 'Content with videos',
            'attachments' => [
                UploadedFile::fake()->create('video.mp4', 5000, 'video/mp4'),
            ],
        ]);

        $response->assertCreated();

        $blog = Blog::where('title', 'Blog with Videos')->first();
        $this->assertNotNull($blog);
        $this->assertCount(1, $blog->attachments);

        $attachment = $blog->attachments->first();
        $this->assertEquals('video', $attachment->file_type);
        $this->assertNotNull($attachment->file_path);
    }

    /**
     * Test user can create a blog with mixed attachment types.
     */
    public function test_user_can_create_blog_with_mixed_attachments(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/blogs', [
            'title' => 'Mixed Media Blog',
            'content' => 'Blog with images and videos',
            'attachments' => [
                UploadedFile::fake()->create('photo.jpg', 500, 'image/jpeg'),
                UploadedFile::fake()->create('video.mp4', 2000, 'video/mp4'),
            ],
        ]);

        $response->assertCreated();

        $blog = Blog::where('title', 'Mixed Media Blog')->first();
        $attachments = $blog->attachments()->get();
        $this->assertCount(2, $attachments);

        // Verify we have one image and one video
        $imageCount = $attachments->where('file_type', 'image')->count();
        $videoCount = $attachments->where('file_type', 'video')->count();
        $this->assertEquals(1, $imageCount);
        $this->assertEquals(1, $videoCount);
    }

    /**
     * Test attachments are returned with file_url in responses.
     */
    public function test_attachments_include_file_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/blogs', [
            'title' => 'Blog with URL Check',
            'content' => 'Testing URLs',
            'attachments' => [
                UploadedFile::fake()->create('test.jpg', 500, 'image/jpeg'),
            ],
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'blog' => [
                        'id',
                        'title',
                        'content',
                        'attachments' => [
                            '*' => [
                                'id',
                                'name',
                                'file_path',
                                'file_type',
                                'file_url',
                            ],
                        ],
                    ],
                ],
            ]);

        // Verify file_url contains asset path
        $attachments = $response->json('data.blog.attachments');
        $this->assertNotEmpty($attachments);
        $this->assertStringContainsString('storage/', $attachments[0]['file_url']);
    }

    /**
     * Test validation error for invalid file format.
     */
    public function test_invalid_file_format_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/blogs', [
            'title' => 'Invalid File Blog',
            'content' => 'Testing validation',
            'attachments' => [
                UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf'),
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['attachments.0']);
    }

    /**
     * Test validation error for file exceeding size limit.
     */
    public function test_file_exceeding_size_limit_rejected(): void
    {
        $user = User::factory()->create();

        // Create a file larger than 20MB
        $response = $this->actingAs($user)->postJson('/api/blogs', [
            'title' => 'Large File Blog',
            'content' => 'Testing size',
            'attachments' => [
                UploadedFile::fake()->create('huge_video.mp4', 21000, 'video/mp4'),
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['attachments.0']);
    }

    /**
     * Test user can update blog and add new attachments.
     */
    public function test_user_can_update_blog_and_add_attachments(): void
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->putJson("/api/blogs/{$blog->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'attachments' => [
                UploadedFile::fake()->create('new_photo.jpg', 500, 'image/jpeg'),
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Blog updated successfully',
            ]);

        $blog->refresh();
        $this->assertEquals('Updated Title', $blog->title);
        $this->assertCount(1, $blog->attachments);
        $this->assertEquals('image', $blog->attachments->first()->file_type);
    }

    /**
     * Test blog show includes attachments.
     */
    public function test_blog_show_includes_attachments(): void
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->create(['user_id' => $user->id]);

        $blog->attachments()->create([
            'name' => 'test_image.jpg',
            'file_path' => 'blogs/attachments/test_image.jpg',
            'file_type' => 'image',
        ]);

        $response = $this->actingAs($user)->getJson("/api/blogs/{$blog->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'blog' => [
                        'attachments' => [
                            '*' => [
                                'id',
                                'name',
                                'file_type',
                                'file_url',
                            ],
                        ],
                    ],
                ],
            ]);

        $attachments = $response->json('data.blog.attachments');
        $this->assertCount(1, $attachments);
        $this->assertEquals('image', $attachments[0]['file_type']);
    }

    /**
     * Test blog index includes user handle.
     */
    public function test_blog_index_includes_user_handle(): void
    {
        $user = User::factory()->create(['handle' => 'test_user_handle']);
        Blog::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/blogs');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'blogs' => [
                        'data' => [
                            '*' => [
                                'user' => [
                                    'id',
                                    'handle',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $blogs = $response->json('data.blogs.data');
        $this->assertEquals('test_user_handle', $blogs[0]['user']['handle']);
    }

    /**
     * Test blog show includes user handle.
     */
    public function test_blog_show_includes_user_handle(): void
    {
        $user = User::factory()->create(['handle' => 'blog_author']);
        $blog = Blog::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson("/api/blogs/{$blog->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'blog' => [
                        'user' => [
                            'handle' => 'blog_author',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Test unauthenticated user cannot create blog.
     */
    public function test_unauthenticated_user_cannot_create_blog(): void
    {
        $response = $this->postJson('/api/blogs', [
            'title' => 'Unauthorized Blog',
            'content' => 'Should fail',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test blog title is required.
     */
    public function test_blog_title_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/blogs', [
            'content' => 'Missing title',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }

    /**
     * Test blog content is required.
     */
    public function test_blog_content_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/blogs', [
            'title' => 'Missing Content',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['content']);
    }
}
