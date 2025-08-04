# <img src="https://raw.githubusercontent.com/nexxtmove/ai/logo/icon.svg" alt="Nexxtmove" height="20"> Nexxtmove AI

## Install

```bash
composer require nexxtmove/ai
```

## Usage

```php
AI::ask('How did we do last month?')
    ->using(Provider::OpenAI, 'gpt-4o')
    ->conversation($conversationModel)
    ->tools(GetOrders::class, GetReturns::class)
    ->output(SalesReport::class)
    ->get();
```
