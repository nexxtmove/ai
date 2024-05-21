# Laravel AI guide

## Use a different model
Set `AI_MODEL` in your `.env` file:
```bash
AI_MODEL=gpt-4o
```

## Quickly test prompts
Use the `php artisan ai:ask` command:
```bash
php artisan ai:ask "Give me a JSON array with three fruits"
```
