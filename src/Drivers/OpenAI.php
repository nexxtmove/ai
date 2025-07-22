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

    public function __construct()
    {
        $this->http = Http::baseUrl('https://api.openai.com/v1')
            ->withToken(config('ai.openai.api_key'));
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
        $model = $options['model'] ?? config('ai.openai.default_model');
        $tools = $options['tools'] ?? [];

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
                'tools' => array_map(fn(Tool $tool) => $this->formatTool($tool), $tools),
            ];

            if (isset($options['output_schema'])) {
                $data['text'] = [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'output_schema',
                        'schema' => $options['output_schema'],
                        'strict' => true,
                    ]
                ];
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

            if (isset($options['output_schema'])) {
                $result = json_decode($result, true);
            }

            return $result;
        }
    }
}
