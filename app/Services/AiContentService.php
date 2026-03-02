<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentService
{
    protected string $provider;
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->provider = Setting::get('ai', 'provider', 'openai');
        $this->apiKey   = Setting::get('ai', 'api_key', '');
        $this->model    = Setting::get('ai', 'model', 'gpt-4o-mini');
    }

    public static function isEnabled(): bool
    {
        return (bool) Setting::get('ai', 'enabled', false);
    }

    public function generate(string $prompt, bool $json = false): ?string
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('AI API key is not configured.');
        }

        return match ($this->provider) {
            'openai'    => $this->callOpenAi($prompt, $json),
            'gemini'    => $this->callGemini($prompt, $json),
            'anthropic' => $this->callAnthropic($prompt, $json),
            default     => throw new \RuntimeException("Unsupported AI provider: {$this->provider}"),
        };
    }

    /* ------------------------------------------------------------------ */
    /*  Provider Implementations                                          */
    /* ------------------------------------------------------------------ */

    protected function callOpenAi(string $prompt, bool $json): ?string
    {
        $payload = [
            'model'    => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant for a game/software e-commerce store. Respond concisely and accurately.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.4,
        ];

        if ($json) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', $payload);

        if ($response->failed()) {
            Log::error('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('OpenAI API request failed: ' . $response->json('error.message', 'Unknown error'));
        }

        return $response->json('choices.0.message.content');
    }

    protected function callGemini(string $prompt, bool $json): ?string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'temperature' => 0.4,
            ],
        ];

        if ($json) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        $response = Http::timeout(30)->post($url, $payload);

        if ($response->failed()) {
            Log::error('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Gemini API request failed: ' . $response->json('error.message', 'Unknown error'));
        }

        return $response->json('candidates.0.content.parts.0.text');
    }

    protected function callAnthropic(string $prompt, bool $json): ?string
    {
        $systemPrompt = 'You are a helpful assistant for a game/software e-commerce store. Respond concisely and accurately.';
        if ($json) {
            $systemPrompt .= ' Always respond with valid JSON only, no markdown fences.';
        }

        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model'      => $this->model,
            'max_tokens' => 2048,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if ($response->failed()) {
            Log::error('Anthropic API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Anthropic API request failed: ' . $response->json('error.message', 'Unknown error'));
        }

        return $response->json('content.0.text');
    }

    /* ------------------------------------------------------------------ */
    /*  Prompt Builders                                                    */
    /* ------------------------------------------------------------------ */

    public function generateShortDescription(string $title): string
    {
        $prompt = "Write a short, compelling product summary (max 250 characters) for \"{$title}\". "
                . "It must be a single sentence or two, marketing-style, highlighting key selling points. "
                . "Suitable for a product card in an e-commerce store. "
                . "Return ONLY the plain text, no quotes, no HTML, no headings.";

        return trim($this->generate($prompt), " \"\n\r");
    }

    public function generateDescription(string $title, string $shortDescription = ''): string
    {
        $context = $shortDescription ? " Context: \"{$shortDescription}\"." : '';

        $prompt = <<<PROMPT
You are an expert e-commerce SEO copywriter. Write a compelling, SEO-optimized product description for "{$title}".{$context}

STRICT HTML FORMAT RULES (Quill editor compatible):
- Use ONLY these HTML tags: <h3>, <p>, <strong>, <em>, <br>
- Do NOT use <ul>, <li>, <ol>, <div>, <span>, <h1>, <h2>, or any other tags
- For listing features, write them as a <p> tag with each item on a new line using <br> and a bullet character like "• " or "✓ "
- Example feature list format: <p>• Feature one description<br>• Feature two description<br>• Feature three description</p>

CONTENT RULES:
- Start with 1-2 engaging intro paragraphs that naturally include the product name (good for SEO)
- Add 2-3 sections with <h3> headings. Write descriptive, keyword-rich headings
- Every section MUST have detailed content directly after the heading — NEVER leave a section empty
- Include specific details, specifications, benefits — avoid generic filler text
- Write naturally but include relevant search keywords throughout
- Use <strong> to emphasize key benefits or specs within paragraphs
- End with a short call-to-action paragraph encouraging purchase
- Do NOT include the product title as an <h1>
- Do NOT include "Short Description" anywhere
- Total length: 250-400 words

Return ONLY the raw HTML. No markdown fences. No backticks. No explanation.
PROMPT;

        $result = trim($this->generate($prompt));
        $result = preg_replace('/^```(?:html)?\s*|\s*```$/s', '', $result);

        return $result;
    }

    public function generateSystemRequirements(string $title): array
    {
        $prompt = "For the game/software \"{$title}\", provide realistic system requirements. "
                . "Return a JSON object with this exact structure: "
                . '{"minimum":[{"key":"os","value":"..."},{"key":"processor","value":"..."},{"key":"memory","value":"..."},{"key":"graphics","value":"..."},{"key":"storage","value":"..."}],'
                . '"recommended":[{"key":"os","value":"..."},{"key":"processor","value":"..."},{"key":"memory","value":"..."},{"key":"graphics","value":"..."},{"key":"storage","value":"..."}],'
                . '"extra":[{"key":"directx","value":"..."},{"key":"network","value":"..."}]} '
                . "Use realistic specs. Return ONLY valid JSON.";

        $raw = $this->generate($prompt, true);
        $raw = preg_replace('/^```(?:json)?\s*|\s*```$/s', '', trim($raw));

        $data = json_decode($raw, true);
        if (!$data || !isset($data['minimum'])) {
            throw new \RuntimeException('AI returned invalid system requirements format.');
        }

        return $data;
    }

    public function generateSeo(string $title, string $shortDescription = ''): array
    {
        $context = $shortDescription ? " Product summary: \"{$shortDescription}\"." : '';

        $prompt = <<<PROMPT
You are an SEO expert. Generate optimized SEO metadata for a product titled "{$title}".{$context}

Return a JSON object with exactly these fields:
{
  "meta_title": "SEO title — max 60 characters, include product name + buying intent keyword like 'Buy', 'Best Price', 'CD Key', 'License'",
  "meta_description": "SEO description — max 155 characters, compelling search snippet that encourages clicks, include product name and a benefit",
  "meta_keywords": "comma-separated keywords, 6-10 relevant long-tail and short keywords for this product"
}

Return ONLY valid JSON. No markdown. No explanation.
PROMPT;

        $raw = $this->generate($prompt, true);
        $raw = preg_replace('/^```(?:json)?\s*|\s*```$/s', '', trim($raw));

        $data = json_decode($raw, true);
        if (!$data || !isset($data['meta_title'])) {
            throw new \RuntimeException('AI returned invalid SEO format.');
        }

        return $data;
    }

    /* ------------------------------------------------------------------ */
    /*  Ticket Reply Generator                                             */
    /* ------------------------------------------------------------------ */

    public function generateTicketReply(array $context): string
    {
        $subject    = $context['subject'] ?? '';
        $department = $context['department'] ?? 'general';
        $priority   = $context['priority'] ?? 'medium';
        $customer   = $context['customer_name'] ?? 'Customer';
        $messages   = $context['messages'] ?? [];
        $instruction = $context['instruction'] ?? '';

        $history = '';
        foreach (array_slice($messages, -8) as $m) {
            $role = ucfirst($m['role'] ?? 'customer');
            $history .= "[{$role}]: {$m['message']}\n";
        }

        $instructionLine = $instruction
            ? "\nADDITIONAL INSTRUCTION FROM AGENT: {$instruction}\n"
            : '';

        $prompt = <<<PROMPT
You are a professional, empathetic customer-support agent for a game/software e-commerce platform called GameHub.

TICKET CONTEXT:
- Subject: {$subject}
- Department: {$department}
- Priority: {$priority}
- Customer Name: {$customer}

CONVERSATION SO FAR:
{$history}
{$instructionLine}
Write a helpful, professional reply to the customer's latest message. Follow these rules:

1. Address the customer by first name naturally (not every sentence)
2. Be empathetic and solution-oriented
3. If the issue requires investigation, acknowledge the problem and explain next steps clearly
4. For order/payment issues, mention you're looking into it and provide expected timeframes
5. For product questions, give specific helpful information
6. Keep the tone warm but professional — avoid overly casual language
7. Do NOT use any HTML formatting — plain text only
8. Do NOT use greetings like "Dear" or sign off with "Best regards" — keep it conversational
9. Keep it concise: 2-4 short paragraphs max
10. If the conversation already has admin replies, maintain continuity and don't repeat what was already said

Return ONLY the reply text. No quotes. No markdown. No labels.
PROMPT;

        return trim($this->generate($prompt), " \"\n\r");
    }

    /* ------------------------------------------------------------------ */
    /*  Blog-Specific Generators                                          */
    /* ------------------------------------------------------------------ */

    public function generateBlogContent(string $title): string
    {
        $prompt = <<<PROMPT
You are an expert blog writer for a gaming and software e-commerce site. Write a well-structured, engaging blog post titled "{$title}".

STRICT HTML FORMAT RULES (Quill editor compatible):
- Use ONLY these HTML tags: <h2>, <h3>, <p>, <strong>, <em>, <br>
- Do NOT use <ul>, <li>, <ol>, <div>, <span>, <h1>, or any other tags
- For lists, write them as a <p> tag with each item on a new line using <br> and "• " prefix
- Example: <p>• First point here<br>• Second point here<br>• Third point here</p>

CONTENT RULES:
- Start with a compelling introductory paragraph. Do NOT start with a heading.
- Use <h2> for major sections and <h3> for sub-sections
- Write 4-6 sections with detailed, informative content in each
- Include practical insights, tips, or analysis relevant to the topic
- Use <strong> to emphasize key points
- End with a conclusion or summary paragraph
- Total length: 400-700 words
- Write naturally, avoid keyword stuffing, but keep it SEO-friendly
- Do NOT include the title as an <h1>

Return ONLY raw HTML. No markdown fences. No backticks.
PROMPT;

        $result = trim($this->generate($prompt));
        $result = preg_replace('/^```(?:html)?\s*|\s*```$/s', '', $result);

        return $result;
    }

    public function generateBlogSeo(string $title): array
    {
        $prompt = <<<PROMPT
You are an SEO expert. Generate optimized SEO metadata for a blog post titled "{$title}" on a gaming/software e-commerce site.

Return a JSON object with exactly these fields:
{
  "meta_title": "SEO title — max 60 characters, catchy and click-worthy, include the main topic keyword",
  "meta_description": "SEO description — max 155 characters, compelling snippet that encourages clicks from search results",
  "meta_keywords": "comma-separated keywords, 6-10 relevant keywords for this blog post topic"
}

Return ONLY valid JSON. No markdown. No explanation.
PROMPT;

        $raw = $this->generate($prompt, true);
        $raw = preg_replace('/^```(?:json)?\s*|\s*```$/s', '', trim($raw));

        $data = json_decode($raw, true);
        if (!$data || !isset($data['meta_title'])) {
            throw new \RuntimeException('AI returned invalid SEO format.');
        }

        return $data;
    }
}
