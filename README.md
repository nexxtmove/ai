# <img src="https://raw.githubusercontent.com/nexxtmove/ai/logo/icon.svg" alt="Nexxtmove" height="20"> Nexxtmove AI

## Install

```bash
composer require nexxtmove/ai
```

## Usage

```php
AI::ask('How did we do last month?')
    ->functions([$orderRepository->getByMonth(...)])
    ->output(SalesReport::class)
    ->get();
```

## Output class

Let the AI respond with a class (instead of just a text string).

```php
class SalesReport
{
    public int $sales;
    public float $revenue;
}

AI::ask('How did we do this month?')
    ->output(SalesReport::class)
    ->get();
```

## Function calling

Give the AI access to functions within your application.

```php
class OrderController extends Controller
{
    public function __construct(
        private OrderRepository $orders,
        private Mailer $mailer,
    ) {}

    public function ask(Request $request)
    {
        $allowedFunctions = [
            $this->orders->countByMonth(...),
            $this->mailer->send(...)
        ];

        return AI::ask($request->prompt) // “How did we do this month?”
            ->functions($allowedFunctions)
            ->get();
    }
}
```

> The `(...)` after the function name is PHP 8.1+ [first-class callable syntax](https://www.php.net/manual/en/functions.first_class_callable_syntax.php).

## Remember messages

Store the conversation in your database, to let the AI react on previous messages.

```php
$conversation = Conversation::find($request->conversation_id);

AI::ask('What did I ask yesterday?')
    ->conversation($conversation)
    ->get();
```

> Make sure to execute `php artisan vendor:publish` to publish the needed migration and `php artisan migrate` to run it.
