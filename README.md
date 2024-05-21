# <img src="https://raw.githubusercontent.com/nexxtmove/ai/logo/icon.svg" alt="Nexxtmove" height="20"> Laravel AI
The simplest way to add AI to your Laravel app, using OpenAI or Gemini.

```php
$fruits = AI::ask('Give me a JSON array with three fruits');
```

### 1. Install
```bash
composer require nexxtmove/ai
```

### 2. Add your API key to `.env`
```bash
AI_DRIVER=openai
OPENAI_API_KEY=sk-...
```

### 3. Ask questions
```php
use Nexxtmove\AI;

$summary = AI::ask("Summarize this article: {$article}");
```

### That's it!
Want to read more? [Check out the guide](GUIDE.md).
