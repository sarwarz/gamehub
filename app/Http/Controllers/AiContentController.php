<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiContentService;
use Illuminate\Http\JsonResponse;

class AiContentController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        if (!AiContentService::isEnabled()) {
            return response()->json(['error' => 'AI features are disabled. Enable them in Settings > AI Configuration.'], 403);
        }

        $request->validate([
            'type'  => 'required|in:short_description,description,system_requirements,seo,blog_content,blog_seo,ticket_reply',
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'ticket_context'    => 'nullable|array',
        ]);

        try {
            $ai    = new AiContentService();
            $type  = $request->input('type');
            $title = $request->input('title');
            $short = $request->input('short_description', '');

            $data = match ($type) {
                'short_description'   => ['content' => $ai->generateShortDescription($title)],
                'description'         => ['content' => $ai->generateDescription($title, $short)],
                'system_requirements' => $ai->generateSystemRequirements($title),
                'seo'                 => $ai->generateSeo($title, $short),
                'blog_content'        => ['content' => $ai->generateBlogContent($title)],
                'blog_seo'            => $ai->generateBlogSeo($title),
                'ticket_reply'        => ['content' => $ai->generateTicketReply($request->input('ticket_context', []))],
            };

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
