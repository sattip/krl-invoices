<?php

namespace App\Services;

use Anthropic\Client;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClaudeInvoiceExtractor
{
    protected Client $client;
    protected string $model;

    public function __construct()
    {
        $apiKey = config('services.anthropic.api_key');

        if (empty($apiKey)) {
            throw new Exception('Anthropic API key is not configured.');
        }

        $this->client = Client::factory()->withApiKey($apiKey)->make();
        $this->model = config('services.anthropic.model', 'claude-3-5-sonnet-latest');
    }

    /**
     * Extract invoice data from an image file.
     *
     * @param string $filePath Path to the file in storage
     * @return array
     * @throws Exception
     */
    public function extract(string $filePath): array
    {
        $fullPath = Storage::disk('public')->path($filePath);

        if (!file_exists($fullPath)) {
            throw new Exception("File not found: {$fullPath}");
        }

        $mimeType = $this->getMimeType($fullPath);
        $imageData = base64_encode(file_get_contents($fullPath));

        $prompt = $this->buildPrompt();

        try {
            if ($mimeType === 'application/pdf') {
                $response = $this->client->messages()->create([
                    'model' => $this->model,
                    'max_tokens' => 4096,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'document',
                                    'source' => [
                                        'type' => 'base64',
                                        'media_type' => $mimeType,
                                        'data' => $imageData,
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],
                ]);
            } else {
                $response = $this->client->messages()->create([
                    'model' => $this->model,
                    'max_tokens' => 4096,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'image',
                                    'source' => [
                                        'type' => 'base64',
                                        'media_type' => $mimeType,
                                        'data' => $imageData,
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],
                ]);
            }

            $rawResponse = $response->content[0]->text ?? '';

            return $this->parseResponse($rawResponse);
        } catch (Exception $e) {
            Log::error('Claude API Error', [
                'message' => $e->getMessage(),
                'file' => $filePath,
            ]);

            throw new Exception('Failed to extract invoice data: ' . $e->getMessage());
        }
    }

    /**
     * Build the prompt for invoice extraction.
     */
    protected function buildPrompt(): string
    {
        return <<<PROMPT
Analyze this invoice image and extract the data into the following JSON structure. Be precise with numbers and dates. If a field is not visible or cannot be determined, use null.

Return ONLY valid JSON in this exact format (no markdown, no explanation):

{
  "issuer": {
    "name": "string",
    "vat_number": "string|null",
    "address": "string|null"
  },
  "customer": {
    "name": "string|null",
    "vat_number": "string|null",
    "address": "string|null"
  },
  "invoice_number": "string|null",
  "invoice_date": "YYYY-MM-DD|null",
  "currency": "string|null",
  "line_items": [
    {
      "description": "string",
      "quantity": number,
      "unit_price": number,
      "vat_rate": number|null,
      "line_total": number
    }
  ],
  "discounts": [
    {
      "description": "string",
      "amount": number
    }
  ],
  "other_charges": [
    {
      "description": "string",
      "amount": number
    }
  ],
  "totals": {
    "subtotal": number,
    "vat_total": number,
    "grand_total": number
  }
}

Important:
- All numeric values should be numbers (not strings)
- Dates must be in YYYY-MM-DD format
- VAT rates should be percentages (e.g., 20 for 20%)
- Include shipping, handling, or service fees in other_charges
- Discounts should be positive numbers (the amount to subtract)
- If no discounts exist, return an empty array []
- If no other charges exist, return an empty array []
- Currency should be a 3-letter code like "USD", "EUR", "GBP"
PROMPT;
    }

    /**
     * Parse the Claude response into structured data.
     *
     * @param string $rawResponse
     * @return array
     * @throws Exception
     */
    protected function parseResponse(string $rawResponse): array
    {
        // Try to extract JSON from the response
        $jsonString = $rawResponse;

        // Remove potential markdown code blocks
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $rawResponse, $matches)) {
            $jsonString = $matches[1];
        }

        $jsonString = trim($jsonString);

        $data = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to parse Claude response', [
                'error' => json_last_error_msg(),
                'raw_response' => $rawResponse,
            ]);

            throw new Exception('Failed to parse invoice data: ' . json_last_error_msg());
        }

        // Validate required fields
        if (!isset($data['issuer']['name'])) {
            throw new Exception('Invoice must have an issuer name');
        }

        // Add raw response for debugging
        $data['raw_response'] = $rawResponse;

        return $data;
    }

    /**
     * Get the MIME type of a file.
     */
    protected function getMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            default => mime_content_type($path) ?: 'application/octet-stream',
        };
    }
}
