# The simplest way to use AI in your Laravel app

```php
$fruits = AI::ask('Give me a JSON array with three fruits');
```

### 1. Install
```
composer require nexxtmove/ai
```

### 2. Add your OpenAI API key to `.env`
```
OPENAI_API_KEY=sk-...
```

### 3. Ask questions
```php
use Nexxtmove\AI;

$summary = AI::ask("Summarize this article: {$article}");
```

### That's it! Easy, right?
Want to configure a bit more? Read this guide.
