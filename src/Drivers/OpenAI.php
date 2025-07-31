<?php

namespace Nexxtmove\AI\Drivers;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Nexxtmove\AI\Tool;

class OpenAI extends AIDriver
{
    private PendingRequest $http;
    
    private ?string $conversationId = null;
    private array $tools = [];
    private ?string $instructions = null;
    private ?array $outputSchema = null;
    private ?string $model = null;

    public function __construct()
    {
        $this->http = Http::baseUrl('https://api.openai.com/v1')
            ->withToken(config('ai.openai.api_key'));
    }

    public function conversation(?string $conversationId): self
    {
        $this->conversationId = $conversationId;
        return $this;
    }

    public function tools(array $tools): self
    {
        $this->tools = $tools;
        return $this;
    }

    public function instruct(string $instructions): self
    {
        $this->instructions = $instructions;
        return $this;
    }

    public function outputSchema(array $schema): self
    {
        $this->outputSchema = $schema;
        return $this;
    }

    public function model(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    protected function callTool(array $availableTools, string $toolName, array $parameters): string
    {
        $tool = collect($availableTools)->first(fn(Tool $t) => $t->name() === $toolName);

        if (!$tool) {
            throw new Exception("Tool not found: $toolName");
        }

        return $tool->call($parameters);
    }

    protected function formatTool(Tool $tool): array
    {
        return [
            'type' => 'function',
            'name' => $tool->name(),
            'description' => $tool->description(),
            'parameters' => $tool->parameters(),
        ];
    }

    public function ask(string $prompt, array $options = []): string|array
    {
        $model = $this->model ?? config('ai.openai.default_model');
        $tools = $this->tools;
        $instructions = $this->instructions;
        $outputSchema = $this->outputSchema;

        if (!$model) {
            throw new Exception('Model is not specified.');
        }

        $messages = [
            ['role' => 'user', 'content' => $prompt],
        ];

        while (true) {
            $data = [
                'model' => $model,
                'input' => $messages,
            ];

            if (!empty($tools)) {
                $data['tools'] = array_map(fn(Tool $tool) => $this->formatTool($tool), $tools);
            }

            if ($outputSchema) {
                $data['text'] = [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'output_schema',
                        'schema' => $outputSchema,
                        'strict' => true,
                    ]
                ];
            }

            if ($instructions) {
                $data['instructions'] = $instructions;
            }

            $response = $this->http->post('responses', $data);

            $response->throw();

            $responseData = $response->json();
            $output = $responseData['output'][0];

            // Tool call handling
            if ($output['type'] === 'function_call' && $output['status'] === 'completed') {
                $toolParameters = json_decode($output['arguments'], true);
                $toolResult = $this->callTool($tools, $output['name'], $toolParameters);

                $messages[] = $output;
                $messages[] = [
                    'type' => 'function_call_output',
                    'call_id' => $output['call_id'],
                    'output' => $toolResult,
                ];

                continue;
            }

            $result = $responseData['output'][0]['content'][0]['text'];

            if ($outputSchema) {
                $result = json_decode($result, true);
            }

            // Reset instance properties after use for a clean state
            $this->reset();

            return $result;
        }
    }

    private function reset(): void
    {
        $this->conversationId = null;
        $this->tools = [];
        $this->instructions = null;
        $this->outputSchema = null;
        $this->model = null;
    }
}
