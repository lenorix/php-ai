# Lenorix AI SDK for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lenorix/ai.svg?style=flat-square)](https://packagist.org/packages/lenorix/ai)
[![Tests](https://img.shields.io/github/actions/workflow/status/lenorix/ai/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/lenorix/ai/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/lenorix/ai.svg?style=flat-square)](https://packagist.org/packages/lenorix/ai)

TypeScript has Vercel AI SDK, Python has LangChain, and PHP has Lenorix AI SDK.

## Support us

We are crafting open-source software for everyone, releasing it under the [Unlicense](LICENSE.md)
 to let it in the public domain. If you find our work useful, consider supporting us.

## Implementations

| Service   | Chat | Streaming | Tools | Vision | Caching | PDF | Structured Output | MCP Client |
|-----------|------|-----------|-------|--------|---------|-----|-------------------|------------|
| OpenAI    | ✓    | ✕         | ✓     | ✕      | ✕       | ✕   | ✕                 | ✕          |
| DeepSeek  | ✓    | ✕         | ✓     | ✕      | ✕       | ✕   | ✕                 | ✕          |
| Anthropic | ✕    | ✕         | ✕     | ✕      | ✕       | ✕   | ✕                 | ✕          |
| Groq      | ✕    | ✕         | ✕     | ✕      | ✕       | ✕   | ✕                 | ✕          |
| Mistral   | ✕    | ✕         | ✕     | ✕      | ✕       | ✕   | ✕                 | ✕          |
| xAI       | ✕    | ✕         | ✕     | ✕      | ✕       | ✕   | ✕                 | ✕          |
| Google    | ✕    | ✕         | ✕     | ✕      | ✕       | ✕   | ✕                 | ✕          |

## Installation

You can install the package via composer:

```bash
composer require lenorix/ai
```

## Usage

```php
use Lenorix\Ai\Provider\DeepSeek;
use Lenorix\Ai\AiText;
use Lenorix\Ai\Chat\ToolFromLambda;

AiText::generate(
    provider: new DeepSeek('deepseek-chat', 'sk-********************************'),
    tools: [
        // This is only an example, you can create your tool extending from CoreTool base class.
        new ToolFromLambda(
            fn ($city = 'madrid') => $city . ' is sunny',
            'weather',
            'get weather updated'
        ),
    ],
    system: 'tell me the weather with updated information',
    messages: [ ['role'=>'user', 'content'=>'tell me'] ],
    maxSteps: 50
);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jesus Hernandez](https://github.com/jhg)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
